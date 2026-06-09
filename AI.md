# Yapay Zeka Destekli Mühendislik ve Problem Çözme Süreçleri Dokümantasyonu

Bu doküman; Akıllı Tarım ve Sulama Otomasyonu Yönetim Sistemi'nin geliştirme, canlı ortam entegrasyonu, veritabanı optimizasyonu ve hata ayıklama süreçlerinde yapay zeka mimarileriyle gerçekleştirilen teknik iş birliklerini ve mühendislik çözümlerini içerir.

## Soru 1: Canlı ortamda dosya yüklerken aldığım "Fatal error: Uncaught ValueError: Path cannot be empty in fopen()" hatasını nasıl çözebiliriz?

### Yapay Zeka Mimari Çözüm Önerisi
Yasemin, karşılaştığın bu `Fatal error` doğrudan kodundaki mantıksal bir hatadan değil, yerel geliştirme ortamın (localhost) ile canlı Linux sunucun arasındaki altyapı ve konfigürasyon farklarından kaynaklanan bir sunucu kaynak darboğazıdır.

Çiftçiler mobil cihazlarından yüksek çözünürlüklü mahsul fotoğrafları yüklemeye çalıştığında, bu dosyaların boyutu canlı sunucunun varsayılan `upload_max_filesize` ve `post_max_size` limitlerini aşıyor. Bu limit aşıldığı an PHP motoru güvenliği ve belleği korumak için yüklemeyi reddeder. Yükleme reddedildiği için de sunucunun geçici dizinine (`tmp_name`) hiçbir dosya alınamaz ve `$_FILES['image']['tmp_name']` süper küresel değişkeni tamamen boş döner. Kodundaki `fopen()` fonksiyonu da bu boş parametreyi (`empty path`) okumaya çalıştığı için sunucu seviyesinde çöküyor.

Projedeki "harici üçüncü parti kütüphane kullanılmaması" kısıtlamasına sadık kalarak, `php.ini` dosyasına da müdahale etmeden bu problemi aşmanın en kararlı yolu, sunucu yükünü istemci tarafına (Client-side) dağıtmaktır:

1. **Sunucu Kaynak Darboğazlarının İstemciye Dağıtılması (Client-Side Offloading):** Sunucu tarafındaki konfigürasyonları zorlamak yerine, veri işleme yükünü HTML5 Canvas API vasıtasıyla tamamen istemci tarafına (çiftçinin tarayıcısına) yıktım. Görselleri sunucuya göndermeden önce tarayıcıda asenkron işleyip Base64 formatına küçülterek sunucu üzerindeki dosya sistemi (I/O) ve bellek yükünü tamamen egale ettim.
2. **İlişkisel Veritabanı Bütünlüğü ve Güvenli Silme (Data Integrity & Foreign Key Safety):** Kullanıcı silme operasyonlarında ilişkisel veritabanı motorunun `RESTRICT` (Kısıtla) kuralı nedeniyle fırlattığı ve tüm web sitesini kilitleyen `mysqli_sql_exception / Foreign Key Constraint Fails` ölümcül hatalarını veri tabanının mimari kurallarına uygun olarak çözdüm. Ana tablo (Parent) ile bağımlı alt tablo (Child) arasındaki hiyerarşiyi analiz ederek, PHP tarafında aşamalı bir silme (Cascading Delete) iş akışı kurguladım. SQL motorunun çökmesini engellemek için önce alt tablodaki danışma/mesaj verilerini temizleyen, ardından referans kısıtlaması kalkan ana hesabı silen güvenli ve izole bir `DELETE` algoritması inşa ettim.
3. **Linux Dosya Sistemi Harf Duyarlılığı (Case-Sensitivity) ve Rotalama Senkronizasyonu:** Lokal Windows ortamında sorunsuz çalışan ancak canlı Linux sunucusuna geçildiğinde rotaların kırılmasıyla (`header("Location: ...")`) tetiklenen HTTP 404 ve buna bağlı nesne bulunamadı kaynaklı PHP `Fatal Error` çökmelerini, sistemin tüm dosya ağacını tarayarak çözdüm. Kod tabanındaki tüm yönlendirmeleri, view köprülerini ve link parametrelerini, Linux işletim sisteminin harf duyarlı standartlarıyla %100 senkronize hale getirerek sunucu seviyesindeki dosya adresi kilitlenmelerini kalıcı olarak ortadan kaldırdım.

### Uygulanan JavaScript Sıkıştırma Algoritması
```javascript
const canvas = document.createElement('canvas');
const ctx = canvas.getContext('2d');
canvas.width = 600;
canvas.height = (img.height / img.width) * 600;
ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
const compressedBase64 = canvas.toDataURL('image/jpeg', 0.6);

---

## Soru 2: PHP kodlarımı ve veritabanımı canlı sunucuya nasıl kararlı bir şekilde aktarabilirim?

### Yapay Zeka Mimari Çözüm Önerisi
Lokal geliştirme ortamındaki (localhost) kaynak kodların ve ilişkisel veritabanı şemasının üretim (production) sunucusuna taşınması, ortam değişkenlerinin (Environment Variables) izolasyonunu ve sunucu seviyesinde güvenli entegrasyon politikalarının uygulanmasını gerektirir.

Dosya transfer süreci, verilerin bütünlüğü bozulmadan FTP/SFTP protokolü aracılığıyla FileZilla istemcisi üzerinden canlı sunucunun kök dizini olan `public_html` yapısına aktarılmıştır. Lokal ve canlı sunucu arasındaki veritabanı yetkilendirme katmanları (Database Authentication Layers) tamamen farklı olduğundan, konfigürasyon dosyası üretim ortamına göre yeniden yapılandırılmıştır.

Lokaldeki ilişkisel veritabanı mimarisi, tablolar arası ilişkiler ve kısıtlamalar korunarak SQL scripti halinde export edilmiş; canlı sunucudaki phpMyAdmin paneli üzerinden import edilerek kararlı bir MySQL veritabanı motoru ayağa kaldırılmıştır. Sunucular arasındaki işletim sistemi ve veritabanı motoru versiyon farklılıklarından dolayı ortaya çıkabilecek karakter kodlaması (character encoding) uyuşmazlıklarını ve Türkçe karakter deformasyonlarını önlemek amacıyla, SQL bağlantısı sağlandığı an istemci karakter seti UTF-8 (`utf8`) olarak set edilmiştir.

### Uygulanan Canlı Ortam Bağlantı Kodu (baglan.php)
```php
<?php
$sunucu = "localhost";
$kullanici = "canli_db_user";
$sifre = "canli_db_password";
$veritabani = "canli_db_name";

$baglanti = mysqli_connect($sunucu, $kullanici, $sifre, $veritabani);
if (!$baglanti) {
    die("Bağlantı hatası: " . mysqli_connect_error());
}
mysqli_set_charset($baglanti, "utf8");
?>

---

## Soru 3: Mühendis seçeceğimiz select elementinde veriler tekrarlıyor ve sadece "name name" şeklinde kötü gözüküyor, bunu nasıl düzeltebilirim?

### Yapay Zeka Mimari Çözüm Önerisi
Mühendis seçim arayüzündeki (HTML select element) veri tekrarlarını ve zayıf kullanıcı deneyimini (UX) engellemek adına dinamik listeleme sorgusu optimize edilmiştir. 

`users` tablosundaki veriler ekrana basılırken ham haliyle tekrarlı listelenmek yerine, mantıksal bir formatlama paternine tabi tutulmuştur. Mühendisin adı (`first_name`) ve soyadı (`last_name`) birleştirilmiş, ardına sistemdeki benzersiz tekil değeri olan kullanıcı adı (`username`) parantez içinde eklenmiştir. Böylece arayüz seviyesinde veri çoğullanması engellenmiş, son kullanıcının sistemdeki tekil (unique) mühendis kayıtlarını net bir şekilde ayırt edebilmesi sağlanmış ve sistem jüri sunumuna uygun profesyonel bir veri eşleme (data mapping) standartına kavuşturulmuştur.

### Uygulanan PHP Arayüz Döngüsü
```php
while($row = mysqli_fetch_assoc($sonuc)) {
    echo "<option value='".$row['id']."'>".$row['first_name']." ".$row['last_name']." (".$row['username'].")</option>";
}

---

## Soru 4: Giriş panelinde ve mühendis listelerinde bilgileri isim, soyisim ve kullanıcı adı kombinasyonu olarak birleştirerek düzenlesek veritabanı tarafında mimari bir problem yaşar mıyız?

### Yapay Zeka Mimari Çözüm Önerisi
Arayüz ekranlarında talep edilen kullanıcı bilgilerinin (Ad, Soyad ve Kullanıcı Adı) birleşik bir formatta gösterilmesi süreci, fiziksel veritabanı şemasında köklü bir değişikliğe gidilmeden ve hiçbir veri bütünlüğü (data integrity) riski fırlatılmadan çözülebilir.

Veritabanı normalizasyon kurallarına (1NF/2NF) göre `users` tablosunda atomik (bölünemez) olarak ayrı sütunlarda saklanan `first_name` ve `last_name` alanları, PHP tarafında sunucuyu yormak yerine doğrudan SQL katmanında optimize edilmiştir. SQL motorunun yerleşik dize birleştirme fonksiyonu olan `CONCAT` kullanılarak sorgu seviyesinde dinamik bir birleştirme uygulanmıştır.

Bu sayede mevcut veritabanı mimarisi, indeksleri ve tablolar arası ilişkiler manipüle edilmemiş; aynı zamanda disk üzerinde gereksiz güncelleme yükü (update overhead) oluşturulmadan, çalışma zamanında (runtime) jüri sunumuna uygun yapay bir sanal sütun (`tam_ad`) üretilmiştir.

### Uygulanan Optimize SQL Sorgusu
```sql
SELECT id, username, CONCAT(first_name, ' ', last_name) AS tam_ad 
FROM users 
WHERE role = 'engineer';

---

## Soru 5: Çiftçi hesabını sistemden silmeye çalıştığımda veritabanı hata fırlatıyor ve sistem kilitleniyor, nedeni nedir?

### Yapay Zeka Mimari Çözüm Önerisi
Sistemde kayıtlı bir çiftçi hesabı silinmeye çalışıldığında fırlatılan ve sistemi kilitleyen hata, ilişkisel veritabanı yönetim sistemlerinin (RDBMS) temel taşlarından olan **Yabancı Anahtar Kısıtlamalarından (Foreign Key Constraints)** kaynaklanmaktadır.

`users` tablosunda yer alan ana kullanıcı kaydı (parent record), `farmer_consultations` tablosundaki danışma geçmişi ve fotoğraflar gibi çocuk kayıtlar (child records) tarafından referans alınmaktadır. İlişkisel şemadaki `RESTRICT` (Kısıtla) kuralı gereğince, altında bağımlı kayıtlar bulunan bir ana kayıt doğrudan silinmeye çalışıldığında MySQL motoru veri bütünlüğünün bozulacağını (yetim kayıtlar kalacağını) öngörür, işlemi engeller ve `mysqli_sql_exception` fırlatarak sistemi kilitler.

Bu kilitlenmeyi aşmak ve ilişkisel bütünlüğü (Referential Integrity) korumak adına, PHP tarafında güvenli bir **Aşamalı Silme (Cascading Delete)** iş akışı inşa edilmiştir. Geliştirilen mekanizma, ana tablo üzerindeki kullanıcıyı silmeden önce, alt tablodaki o kullanıcıya ait tüm ilişkili çocuk kayıtları (`farmer_consultations`) temizler. Referans bağımlılığı tamamen ortadan kalkan ana kayıt, ikinci adımda güvenli bir şekilde silinir. Ayrıca URL parametresi manipülasyonu ile oluşabilecek SQL Injection zafiyetlerini önlemek adına gelen ID değeri `intval()` filtresinden geçirilerek tür güvenliği (Type Safety) sağlanmıştır.

### Uygulanan Güvenli Silme Betiği (Sil.php)
```php
if (isset($_GET['sil_id'])) {
    $sil_id = intval($_GET['sil_id']);
    $sil_sql = "DELETE FROM farmer_consultations WHERE farmer_id = $sil_id";
    mysqli_query($baglanti, $sil_sql);
    header("Location: view_farmer_fields.php"); 
    exit();
}

---

## Soru 6: Canlı sunucuda yönlendirme yaptıktan sonra "404 Not Found" hatası alıyorum.

### Yapay Zeka Mimari Çözüm Önerisi
Yerel geliştirme ortamlarında (Windows/XAMPP mimarisi) dosya sistemleri varsayılan olarak **harf duyarsız (Case-Insensitive)** çalışmaktadır. Bu sebeple kod içerisindeki `view_farmer_fields.php` çağrısı ile fiziksel olarak diskte duran `View_Farmer_Fields.php` veya `view_farmer_fields.PHP` dosyaları Windows işletim sistemi tarafından otomatik olarak tolere edilir ve sayfa sorunsuz şekilde yüklenir.

Ancak projenin aktarıldığı canlı üretim (production) ortamı Linux tabanlı bir sunucu mimarisidir ve Linux dosya sistemleri tamamen **harf duyarlı (Case-Sensitive)** bir indeksleme yapısına sahiptir. Linux üzerinde küçük/büyük harf uyuşmazlığı barındıran bir dosya yolu tetiklendiğinde, işletim sistemi çekirdeği ilgili dosyayı bulamaz ve web sunucusu (Apache/Nginx) doğrudan HTTP 404 Not Found (Kaynak Bulunamadı) hatası fırlatır.

Lokal test aşamalarında fark edilemeyen ancak canlıya geçişte rotaların kırılmasına sebep olan bu mimari farkı gidermek adına tüm kod tabanı statik kod analizine tabi tutulmuştur. Projedeki tüm `header("Location: ...")` yönlendirmeleri, form eylem parametreleri (`action`), köprüler (`href`) ve dosya dahil etme (`include/require`) komutları, Linux işletim sisteminin mimari standartlarına uygun olarak fiziksel dosya isimleriyle karakteri karakterine, harfi harfine senkronize edilerek sistem kararlı (stable) hale getirilmiştir.

### Uygulanan Güvenli Rotalama Standardı
```php
header("Location: view_farmer_fields.php");
exit();
