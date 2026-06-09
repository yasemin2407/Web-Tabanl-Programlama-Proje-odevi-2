# Akıllı Tarım ve Sulama Otomasyonu Yönetim Sistemi 

Bu proje, çiftçiler ile ziraat mühendislerini dijital bir platformda buluşturarak tarımsal verimliliği artırmayı, ürün hastalıklarına hızlı müdahale etmeyi ve tarla/sulama takibini kolaylaştırmayı amaçlayan web tabanlı bir yönetim sistemidir.

## Özellikler

- **Çiftçi Paneli:**
  - Tarla ve mahsul türü kaydı yapabilme.
  - Tarladaki sorunlu mahsulün fotoğrafını çekip/yükleyip mühendise danışabilme.
  - Mühendisten gelen reçete ve cevapları anlık takip edebilme.
- **Mühendis Paneli:**
  - Sorumlu olduğu çiftçileri ve onların tarla/mahsul bilgilerini listeleyebilme.
  - Çiftçilerden gelen mahsul fotoğraflarını ve notlarını görüntüleme.
  - Fotoğraflı sorunlara teşhis koyup reçete/cevap yazabilme.
- **Performans & Güvenlik:**
  - **İstemci Tarafında Resim Sıkıştırma:** Çiftçilerin yüklediği yüksek çözünürlüklü fotoğraflar, sunucuyu yormamak ve kota aşımını engellemek için **HTML5 Canvas API** kullanılarak tarayıcıda anlık olarak optimize edilir ve sıkıştırılır.
  - **Şifre Güvenliği:** Kullanıcı şifreleri veritabanına düz metin olarak değil, `password_hash()` fonksiyonu ile kriptolanarak güvenli bir şekilde kaydedilir.
  - **Oturum Yönetimi:** Kullanıcı yetkilendirmeleri tamamen PHP `Session` mimarisiyle korunmaktadır.

## Kullanılan Yazılım Araçları

- **Backend:** PHP
- **Frontend:** HTML5, CSS3, Bootstrap 5, FontAwesome 
- **Database:** MySQL
- **Optimization:** JavaScript 

```text
📂 akilli-tarim-otomasyonu/
│
├── 📂 includes/              # Ortak Bileşenler ve Şablonlar
│   ├──  footer.php             # Sayfa alt bilgi alanı
│   └──  header.php             # Sayfa üst bilgi alanı
├── 📂 uploads/               # Kullanıcıların yüklediği dokümanlar/görseller
│
├──  add_fields.php           # Yeni tarla/mahsul alanı ekleme modülü
├──  baglan.php               # Veritabanı bağlantı ve ayar dosyası
├──  consult.php              # Çiftçi danışma ve fotoğraf gönderme ekranı
├──  edit_field.php           # Mevcut tarla bilgilerini güncelleme modülü
├──  index.php                # Projenin ana karşılama sayfası
├──  login.php                # Kullanıcı giriş (oturum açma) ekranı
├──  logout.php               # Oturum kapatma ve session temizleme betiği
├──  profile.php              # Kullanıcı profil bilgileri yönetim sayfası
├──  register.php             # Yeni çiftçi/mühendis kayıt ekranı
├──  sensor_rapor.php         # Akıllı sensör verileri raporlama modülü
├──  sulama_kontrol.php       # Otomatik/manuel sulama yönetim paneli
├──  sulama_onay.php          # Mühendis sulama onay ve reçete işleme mekanizması
├──  tarlalarim.php           # Çiftçiye ait kayıtlı tarlaların listelendiği panel
├──  view_farmer_fields.php   # Mühendisin çiftçi tarlalarını incelediği ekran
└──  veritabanı.sql           # Projenin MySQL ilişkisel veritabanı şeması dışa aktarımı
