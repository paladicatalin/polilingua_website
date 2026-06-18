
-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Jobs table
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title_ro VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) DEFAULT '',
    title_en VARCHAR(255) DEFAULT '',
    short_desc_ro TEXT,
    short_desc_ru TEXT,
    short_desc_en TEXT,
    full_desc_ro TEXT,
    full_desc_ru TEXT,
    full_desc_en TEXT,
    location VARCHAR(255) DEFAULT 'Moldova',
    schedule VARCHAR(255) DEFAULT 'Full-time',
    sticky_color VARCHAR(50) DEFAULT '#4CAF82',
    sticky_rotation DECIMAL(5,2) DEFAULT -2.5,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_ro VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) DEFAULT '',
    title_en VARCHAR(255) DEFAULT '',
    description_ro TEXT,
    description_ru TEXT,
    description_en TEXT,
    icon_key VARCHAR(64) NOT NULL DEFAULT 'clipboard-check',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    UNIQUE KEY uniq_services_title_ro (title_ro),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Applications table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    cv_file VARCHAR(255),
    status ENUM('new', 'reviewed', 'interview', 'hired', 'rejected') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL
);

-- Site content table
CREATE TABLE IF NOT EXISTS site_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_key VARCHAR(255) NOT NULL UNIQUE,
    value_ro TEXT,
    value_ru TEXT,
    value_en TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create admin accounts privately with a hashed password.

-- Seed: sample jobs
INSERT INTO jobs (slug, title_ro, title_ru, title_en, short_desc_ro, short_desc_ru, short_desc_en, full_desc_ro, full_desc_ru, full_desc_en, location, schedule, sticky_color, sticky_rotation, sort_order) VALUES
('manager-vanzari', 'Manager de vânzări (5 pm - 1 am)', 'Менеджер по продажам (17:00-01:00)', 'Sales Manager (5pm - 1am)',
'Suntem în căutare unui profesionist în vânzări. Dacă ești ambiţios şi orientat spre rezultate, te aşteptăm în echipa noastră!',
'Мы ищем профессионала в области продаж. Если вы амбициозны и ориентированы на результат, ждем вас в нашей команде!',
'We are looking for a sales professional. If you are ambitious and result-oriented, we welcome you to our team!',
'<p>Rolul tău va fi să gestionezi relațiile cu clienții și să aduci noi oportunități de business companiei.</p><ul><li>Experiență în vânzări minim 1 an</li><li>Cunoașterea limbii române și ruse</li><li>Abilități excelente de comunicare</li></ul>',
'<p>Вы будете управлять отношениями с клиентами и приносить новые бизнес-возможности компании.</p>',
'<p>Your role will be to manage client relationships and bring new business opportunities to the company.</p>',
'Chișinău, Moldova', '5 pm - 1 am', '#4CAF82', -3.5, 1),

('asistent-administrativ', 'Asistent Administrativ / Manager Oficiu', 'Административный ассистент / Офис-менеджер', 'Administrative Assistant / Office Manager',
'Căutăm un asistent administrativ organizat și proactiv pentru a susține operațiunile zilnice ale companiei noastre.',
'Мы ищем организованного и проактивного административного ассистента для поддержки повседневной деятельности компании.',
'We are looking for an organized and proactive administrative assistant to support our company daily operations.',
'<p>Vei fi responsabil pentru coordonarea activităților administrative și menținerea unui mediu de lucru eficient.</p>',
'<p>Вы будете отвечать за координацию административной деятельности.</p>',
'<p>You will be responsible for coordinating administrative activities.</p>',
'Chișinău, Moldova', 'Full-time', '#9B59B6', 2.5, 2)
ON DUPLICATE KEY UPDATE title_ro=title_ro;

-- Seed: services
INSERT INTO services (
  title_ro, title_ru, title_en,
  description_ro, description_ru, description_en,
  icon_key, sort_order, is_active
) VALUES
('Servicii de traducere', 'Услуги перевода', 'Translation services',
 'Traduceri clare și corecte, adaptate domeniului proiectului tău.',
 'Четкие и точные переводы, адаптированные к тематике вашего проекта.',
 'Clear and accurate translations tailored to your project domain.',
 'clipboard-check', 1, 1),
('Localizare', 'Локализация', 'Localization',
 'Adaptăm conținutul pentru publicul local și contextul cultural potrivit.',
 'Адаптируем контент для локальной аудитории и культурного контекста.',
 'We adapt content for local audiences and the right cultural context.',
 'globe', 2, 1),
('Autentificare de documente', 'Аутентификация документов', 'Document authentication',
 'Gestionăm documente oficiale cu atenție la detalii și conformitate.',
 'Работаем с официальными документами с вниманием к деталям и требованиям.',
 'We handle official documents with attention to detail and compliance.',
 'file-text', 3, 1),
('Spoturi publicitare, inclusiv multilingve', 'Рекламные ролики, включая многоязычные', 'Advertising spots, including multilingual',
 'Mesaje persuasive pentru campanii și spoturi, inclusiv versiuni multilingve.',
 'Убедительные тексты для кампаний и роликов, включая многоязычные версии.',
 'Persuasive copy for campaigns and spots, including multilingual versions.',
 'megaphone', 4, 1),
('Traducere și localizare site-uri web', 'Перевод и локализация веб-сайтов', 'Website translation and localization',
 'Traducem și optimizăm conținutul web pentru o experiență coerentă.',
 'Переводим и оптимизируем веб-контент для целостного пользовательского опыта.',
 'We translate and optimize web content for a consistent user experience.',
 'laptop', 5, 1),
('Elaborare site-uri în mai multe limbi', 'Разработка сайтов на нескольких языках', 'Multilingual website development',
 'Dezvoltăm soluții digitale moderne pentru prezență online multilingvă.',
 'Разрабатываем современные цифровые решения для многоязычного онлайн-присутствия.',
 'We build modern digital solutions for multilingual online presence.',
 'network', 6, 1),
('Interpretare și traduceri sincronice: conferințe, întâlniri de afaceri, apeluri video și telefonice, publicații și materiale tripartite etc.', 'Устный и синхронный перевод: конференции, деловые встречи, видео- и телефонные звонки, публикации и трехсторонние материалы и т.д.', 'Interpreting and simultaneous translation for conferences, business meetings, video and phone calls, publications and tripartite materials, etc.',
 'Interpretare profesionistă pentru conferințe, întâlniri și comunicare live.',
 'Профессиональный устный перевод для конференций, встреч и live-коммуникации.',
 'Professional interpreting for conferences, meetings, and live communication.',
 'headset', 7, 1),
('Elaborare website-uri', 'Разработка веб-сайтов', 'Website development',
 'Soluții profesionale de limbă, livrate rapid și cu standarde înalte.',
 'Профессиональные языковые решения с быстрым выполнением и высоким качеством.',
 'Professional language solutions delivered fast with high standards.',
 'toolbox', 8, 1)
ON DUPLICATE KEY UPDATE
title_ru = VALUES(title_ru),
title_en = VALUES(title_en),
description_ro = VALUES(description_ro),
description_ru = VALUES(description_ru),
description_en = VALUES(description_en),
icon_key = VALUES(icon_key),
sort_order = VALUES(sort_order),
is_active = VALUES(is_active);

-- Seed: site content
INSERT INTO site_content (content_key, value_ro, value_ru, value_en) VALUES
('hero_title', 'Descoperim noi oportunități de lucru', 'Открываем новые возможности для работы', 'We discover new job opportunities'),
('hero_subtitle', 'Bun venit la PoliLingua, o companie globală de recrutare profesională care împlinește experiența cu oameni din întreaga lume.', 'Добро пожаловать в PoliLingua, глобальную компанию профессионального рекрутинга.', 'Welcome to PoliLingua, a global professional recruitment company.'),
('jobs_title', 'Posturi vacante', 'Вакансии', 'Job Openings'),
('jobs_subtitle', 'Suntem în căutare continuă de noi profesioniști, atât angajați înalt specializați, cât și noi tinere talente.', 'Мы постоянно ищем новых профессионалов, как высококвалифицированных специалистов, так и молодые таланты.', 'We are continuously looking for new professionals, both highly specialized employees and new young talents.'),
('jobs_empty', 'Nu există posturi vacante momentan.', 'На данный момент открытых вакансий нет.', 'There are no job openings at the moment.'),
('about_title', 'Despre PoliLingua', 'О PoliLingua', 'About PoliLingua'),
('about_text', 'La PoliLingua, folosim o combinație puternică de creativitate umană și inteligență automată pentru a crea traduceri de calitate consecventă în viteză. Echipa noastră talentată este unită prin pasiunea pentru limbă și cultură. Cea mai bună motivație pentru noi este cunoașterea faptului că ajutăm mărcile globale să crească, să se implice și să ajungă la publicul lor internațional, transformând conținutul multilingv pentru ei. Lucrăm cu unele dintre cele mai bune și cunoscute companii din lume și am dezvoltat o cultură de învățare și îmbunătățire continuă, în care cheia principală a succesului este echipa noastră', 'В PoliLingua мы используем мощное сочетание человеческой креативности и автоматизированного интеллекта, чтобы создавать переводы с неизменно высоким качеством и скоростью. Наша талантливая команда объединена страстью к языку и культуре. Для нас лучшая мотивация — осознание того, что мы помогаем глобальным брендам расти, вовлекать и достигать международной аудитории, трансформируя для них многоязычный контент. Мы работаем с одними из самых известных компаний мира и развили культуру постоянного обучения и улучшения, где главным ключом к успеху является наша команда.', 'At PoliLingua, we use a powerful combination of human creativity and automated intelligence to deliver consistently high-quality translations at speed. Our talented team is united by a passion for language and culture. Our strongest motivation is knowing that we help global brands grow, engage, and reach their international audiences by transforming multilingual content for them. We work with some of the best-known companies in the world and have built a culture of continuous learning and improvement, where our team is the main key to success.'),
('services_title', 'Servicii', 'Услуги', 'Services'),
('why_title', 'De ce ar trebui să faci parte din PoliLingua?', 'Почему стоит присоединиться к PoliLingua?', 'Why should you be part of PoliLingua?'),
('careers_title', 'Cariere', 'Карьера', 'Careers'),
('careers_subtitle', 'Descoperă-ți cariera de vis în compania noastră dinamică și inovatoare! Ești în căutarea unei cariere stimulatoare într-o companie care prețuiește inovația și creșterea?', 'Откройте для себя карьеру своей мечты в нашей динамичной и инновационной компании! Вы ищете вдохновляющую карьеру в компании, которая ценит инновации и рост?', 'Discover your dream career in our dynamic and innovative company! Are you looking for a stimulating career in a company that values innovation and growth?'),
('footer_about', 'Colaborăm cu lingviști atent selectați pentru a livra servicii de calitate, adaptate fiecărui proiect.', 'Мы сотрудничаем с тщательно отобранными лингвистами и предоставляем услуги высокого качества для каждого проекта.', 'We work with carefully selected linguists and deliver high-quality services tailored to each project.'),
('contact_title_line1', 'Viitorul tău începe aici!', 'Твое будущее начинается здесь!', 'Your future starts here!'),
('contact_title_line2', 'Fii parte din ceva măreț!', 'Стань частью чего-то великого!', 'Be part of something great!'),
('contact_quote', '„Puterea echipei este fiecare membru individual. Puterea fiecărui membru este echipa.”', '«Сила команды в каждом отдельном участнике. Сила каждого участника — в команде.»', '"The strength of the team is each individual member. The strength of each member is the team."'),
('contact_quote_author', 'Phil Jackson', 'Phil Jackson', 'Phil Jackson'),
('contact_call_label', 'Sună acum', 'Позвонить сейчас', 'Call now'),
('contact_message_label', 'Trimite mesaj', 'Отправить сообщение', 'Send message'),
('contact_phone', '+37369000000', '+37369000000', '+37369000000'),
('contact_email', 'office@polilingua.md', 'office@polilingua.md', 'office@polilingua.md'),
('contact_address', 'Chișinău, Moldova', 'Кишинёв, Молдова', 'Chisinau, Moldova')
ON DUPLICATE KEY UPDATE content_key=content_key;
