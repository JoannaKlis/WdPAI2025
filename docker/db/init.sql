CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    picture_url TEXT,
    enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pets (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    picture_url TEXT,
    pet_type VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    birth_date DATE,
    sex VARCHAR(10) CHECK (sex IN ('Male', 'Female')),
    breed VARCHAR(100),
    color VARCHAR(100),
    microchip_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_weights (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    weight DECIMAL(10,2) NOT NULL,
    unit VARCHAR(10) NOT NULL CHECK (unit IN ('g', 'kg')),
    recorded_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_grooming (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    groom_date DATE NOT NULL,
    groom_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_shearing (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    shearing_date DATE NOT NULL,
    shearing_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_trimming (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    trimming_date DATE NOT NULL,
    trimming_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_vaccinations (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    vaccination_name VARCHAR(200) NOT NULL,
    vaccination_date DATE NOT NULL,
    dose DECIMAL(10,2),
    unit VARCHAR(10) CHECK (unit IN ('ml', 'l', 'mg', 'g')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_treatments (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    treatment_name VARCHAR(200) NOT NULL,
    treatment_date DATE NOT NULL,
    treatment_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_deworming (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    deworming_name VARCHAR(200) NOT NULL,
    deworming_date DATE NOT NULL,
    dose DECIMAL(10,2),
    unit VARCHAR(10) CHECK (unit IN ('ml', 'l', 'mg', 'g')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_visits (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    visit_name VARCHAR(200) NOT NULL,
    visit_date DATE NOT NULL,
    visit_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_sensitivities (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    food VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_favorite_food (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    food VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_supplements (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    supplement_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_feeding_schedule (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    feeding_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE pet_events (
    id SERIAL PRIMARY KEY,
    pet_id INT NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    event_name VARCHAR(200) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- PRZYKŁADOWE DANE
INSERT INTO users (firstname, lastname, email, password, bio, picture_url, enabled)
VALUES (
    'Jan',
    'Kowalski',
    'jan.kowalski@example.com',
    '$2y$10$ejsy0sJ6.EbvvDCVD1rjpu82Xjo1H2JDfjHuMF6jdCB5ymNguYLoq', -- hasło: "test"
    'Miłośnik zwierząt i programowania.',
    'public/img/profile.png',
    TRUE
);

INSERT INTO users (firstname, lastname, email, password, bio, enabled)
VALUES (
    'Anna',
    'Nowak',
    'anna.nowak@example.com',
    '$2y$10$ejsy0sJ6.EbvvDCVD1rjpu82Xjo1H2JDfjHuMF6jdCB5ymNguYLoq',
    'Opiekunka zwierząt.',
    TRUE
);

-- Zwierzaki użytkownika Jana (id=1)
INSERT INTO pets (user_id, picture_url, pet_type, name, birth_date, sex, breed, color, microchip_number)
VALUES 
    (1, 'public/img/simba.png', 'Cat', 'Simba', '2022-06-15', 'Male', 'Maine Coon', 'Orange and White', '616093901234567'),
    (1, 'public/img/dog.png', 'Dog', 'Pumpkin', '2024-12-05', 'Male', 'Welsh Corgi Pembroke', 'Red and White', '9002147294610283');

-- Zwierzak użytkownika Anny (id=2)
INSERT INTO pets (user_id, picture_url, pet_type, name, birth_date, sex, breed, color, microchip_number)
VALUES 
    (2, 'public/img/luna.png', 'Cat', 'Luna', '2021-03-20', 'Female', 'Siamese', 'Cream and Brown', '616093905678901');

-- CARE dla Simby (pet_id=1)
INSERT INTO pet_weights (pet_id, weight, unit, recorded_date, recorded_time)
VALUES 
    (1, 5.2, 'kg', '2025-08-15', '10:00'),
    (1, 4.9, 'kg', '2025-05-03', '14:30');

INSERT INTO pet_grooming (pet_id, name, groom_date, groom_time)
VALUES 
    (1, 'Combing out knots', '2025-08-12', '15:00'),
    (1, 'Comprehensive care', '2025-07-20', '10:30');

INSERT INTO pet_shearing (pet_id, name, shearing_date, shearing_time)
VALUES 
    (1, 'Preparation for a dog show', '2025-06-10', '14:00'),
    (1, 'Classic shearing', '2025-05-29', '11:00');

INSERT INTO pet_trimming (pet_id, name, trimming_date, trimming_time)
VALUES 
    (1, 'Trimming claws 2', '2025-08-18', '16:00'),
    (1, 'Trimming claws 1', '2025-07-01', '09:00');

-- HEALTH BOOK dla Simby (pet_id=1)
INSERT INTO pet_vaccinations (pet_id, vaccination_name, vaccination_date, dose, unit)
VALUES 
    (1, 'Rabies', '2024-08-24', 0.4, 'ml'),
    (1, 'Feline leukemia', '2024-01-15', 0.7, 'ml'),
    (1, 'Feline typhus', '2023-10-02', 0.3, 'ml');

INSERT INTO pet_treatments (pet_id, treatment_name, treatment_date, treatment_time)
VALUES 
    (1, 'Castration', '2024-08-24', '14:00');

INSERT INTO pet_deworming (pet_id, deworming_name, deworming_date, dose, unit)
VALUES 
    (1, 'Internal deworming', '2025-05-15', 230, 'mg');

INSERT INTO pet_visits (pet_id, visit_name, visit_date, visit_time)
VALUES 
    (1, 'Follow-up visit', '2025-10-20', '10:00'),
    (1, 'Deworming', '2025-05-15', '14:00'),
    (1, 'Rabies vaccination', '2024-08-24', '09:00');

-- NUTRITION dla Simby (pet_id=1)
INSERT INTO pet_sensitivities (pet_id, food)
VALUES 
    (1, 'Chicken'),
    (1, 'Beef');

INSERT INTO pet_favorite_food (pet_id, food)
VALUES 
    (1, 'PURINA PRO PLAN');

INSERT INTO pet_supplements (pet_id, supplement_name)
VALUES 
    (1, 'Omega-3'),
    (1, 'Probiotics');

INSERT INTO pet_feeding_schedule (pet_id, name, feeding_time)
VALUES 
    (1, 'Canned cat food', '08:00'),
    (1, 'Cat kibble', '13:00'),
    (1, 'Canned cat food', '18:00');

-- EVENTS dla Simby (pet_id=1)
INSERT INTO pet_events (pet_id, event_name, event_date, event_time)
VALUES 
    (1, 'Trimming claws', '2024-10-05', '14:00'),
    (1, 'Vet checkup', '2024-10-20', '10:00'),
    (1, 'Grooming appointment', '2024-10-12', '15:00');

-- DANE dla Pumpkin (pet_id=2)
INSERT INTO pet_weights (pet_id, weight, unit, recorded_date, recorded_time)
VALUES 
    (2, 12.5, 'kg', '2025-08-20', '11:00');

INSERT INTO pet_vaccinations (pet_id, vaccination_name, vaccination_date, dose, unit)
VALUES 
    (2, 'Rabies', '2025-01-10', 1.0, 'ml');