-- ─────────────────────────────────────────────────────────────────────────────
-- PAWly - Base de datos
-- ─────────────────────────────────────────────────────────────────────────────

CREATE DATABASE IF NOT EXISTS pawly_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE pawly_db;

-- ─────────────────────────────────────────────────────────────────────────────
-- TABLAS
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE usuarios (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100)  NOT NULL,
    email            VARCHAR(150)  NOT NULL UNIQUE,
    password         VARCHAR(255)  NOT NULL,
    telefono         VARCHAR(20)   DEFAULT NULL,
    provincia        VARCHAR(100)  DEFAULT NULL,
    localidad        VARCHAR(100)  DEFAULT NULL,
    biografia        TEXT          DEFAULT NULL,
    foto_perfil      VARCHAR(255)  DEFAULT NULL,
    rol              ENUM('USER','ADMIN') NOT NULL DEFAULT 'USER',
    activo           TINYINT(1)    NOT NULL DEFAULT 1,
    fecha_registro   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE animales (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre             VARCHAR(100)  NOT NULL,
    especie            VARCHAR(50)   NOT NULL,
    raza               VARCHAR(100)  DEFAULT NULL,
    sexo               ENUM('MACHO','HEMBRA') NOT NULL,
    etapa_vida         ENUM('CACHORRO','ADULTO','MAYOR') NOT NULL,
    tamanyo            ENUM('PEQUENO','MEDIANO','GRANDE') NOT NULL,
    color              VARCHAR(50)   NOT NULL,
    descripcion        TEXT          NOT NULL,
    esterilizado       TINYINT(1)    NOT NULL DEFAULT 0,
    vacunado           TINYINT(1)    NOT NULL DEFAULT 0,
    desparasitado      TINYINT(1)    NOT NULL DEFAULT 0,
    microchip          TINYINT(1)    NOT NULL DEFAULT 0,
    imagen             VARCHAR(255)  DEFAULT NULL,
    provincia          VARCHAR(100)  NOT NULL,
    localidad          VARCHAR(100)  NOT NULL,
    estado             ENUM('PENDIENTE_APROBACION','DISPONIBLE','EN_PROCESO','ADOPTADO','RECHAZADO')
                       NOT NULL DEFAULT 'PENDIENTE_APROBACION',
    motivo_rechazo     TEXT          DEFAULT NULL,
    publicador_id      INT UNSIGNED  NOT NULL,
    fecha_publicacion  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publicador_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE favoritos (
    usuario_id     INT UNSIGNED NOT NULL,
    animal_id      INT UNSIGNED NOT NULL,
    fecha_guardado TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, animal_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (animal_id)  REFERENCES animales(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE solicitudes_adopcion (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    animal_id         INT UNSIGNED NOT NULL,
    solicitante_id    INT UNSIGNED NOT NULL,
    publicador_id     INT UNSIGNED NOT NULL,
    mensaje           TEXT         DEFAULT NULL,
    estado            ENUM('PENDIENTE','ACEPTADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
    fecha_solicitud   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id)      REFERENCES animales(id)  ON DELETE CASCADE,
    FOREIGN KEY (solicitante_id) REFERENCES usuarios(id)  ON DELETE CASCADE,
    FOREIGN KEY (publicador_id)  REFERENCES usuarios(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────────────────────
-- USUARIOS
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO usuarios (nombre, email, password, telefono, provincia, localidad, rol) VALUES
('Admin PAWly',      'admin@pawly.com',   '$2y$10$TKh8H1.PfFkH3KpKONh8reMyXxKUh1OdkFZGw.yxRB3nEhLFgEeHu', '600000000', 'Madrid',    'Madrid',           'ADMIN'),
('lgarlaz',          'lgarlaz@pawly.com', '$2y$10$TKh8H1.PfFkH3KpKONh8reMyXxKUh1OdkFZGw.yxRB3nEhLFgEeHu', '600000001', 'Barcelona', 'Barcelona',        'ADMIN'),
('Laura Martínez',   'laura@pawly.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  '611222333', 'Madrid',    'Alcalá de Henares','USER'),
('Carlos Ruiz',      'carlos@pawly.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  '622333444', 'Barcelona', 'Badalona',         'USER'),
('Ana García',       'ana@pawly.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  '633444555', 'Valencia',  'Valencia',         'USER'),
('Miguel Torres',    'miguel@pawly.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  '644555666', 'Sevilla',   'Dos Hermanas',     'USER'),
('Sara López',       'sara@pawly.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  '655666777', 'Málaga',    'Marbella',         'USER'),
('Pedro Sánchez',    'pedro@pawly.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  '666777888', 'Zaragoza',  'Zaragoza',         'USER'),
('Marta Fernández',  'marta@pawly.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  '677888999', 'Bilbao',    'Bilbao',           'USER'),
('Javier Moreno',    'javier@pawly.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  '688999000', 'Granada',   'Granada',          'USER'),
('Isabel Díaz',      'isabel@pawly.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  '699000111', 'Alicante',  'Benidorm',         'USER'),
('Roberto Jiménez',  'roberto@pawly.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  '600111222', 'Murcia',    'Cartagena',        'USER');


-- ─────────────────────────────────────────────────────────────────────────────
-- ANIMALES
-- Nota: publicador_id hace referencia al orden de inserción de usuarios.
-- id 3 = Laura, 4 = Carlos, 5 = Ana, 6 = Miguel, 7 = Sara,
-- id 8 = Pedro, 9 = Marta, 10 = Javier, 11 = Isabel, 12 = Roberto
-- ─────────────────────────────────────────────────────────────────────────────

-- Perros
INSERT INTO animales (nombre, especie, raza, sexo, etapa_vida, tamanyo, color, descripcion, vacunado, esterilizado, desparasitado, microchip, provincia, localidad, estado, publicador_id) VALUES
('Rocky', 'Perro', 'Labrador',        'MACHO',  'ADULTO',   'GRANDE',  'DORADO',    'Labrador de 3 años muy equilibrado y obediente. Sabe los comandos básicos y le encanta el agua. Necesita salidas diarias. Ideal para familias activas.',                    1, 1, 1, 1, 'Barcelona', 'Badalona',      'DISPONIBLE',          4),
('Toby',  'Perro', 'Beagle',          'MACHO',  'CACHORRO', 'MEDIANO', 'TRICOLOR',  'Cachorro de 4 meses lleno de energía. Muy inteligente y fácil de educar. Le encanta jugar con pelotas y explorar el jardín.',                                               1, 0, 1, 1, 'Sevilla',   'Dos Hermanas',  'DISPONIBLE',          6),
('Bolt',  'Perro', 'Mestizo',         'MACHO',  'MAYOR',    'MEDIANO', 'NEGRO',     'Perro de 9 años rescatado de la calle. Muy tranquilo y agradecido. Solo quiere un sofá calentito y una familia que lo quiera.',                                              1, 1, 1, 1, 'Bilbao',    'Bilbao',        'DISPONIBLE',          9),
('Zeus',  'Perro', 'Pastor Alemán',   'MACHO',  'ADULTO',   'GRANDE',  'NEGRO',     'Pastor alemán de 5 años con carácter fuerte pero muy leal. Ha recibido adiestramiento básico. Necesita dueño con experiencia.',                                              1, 1, 1, 1, 'Murcia',    'Cartagena',     'DISPONIBLE',         12),
('Kira',  'Perro', 'Golden Retriever','HEMBRA', 'CACHORRO', 'GRANDE',  'DORADO',    'Golden de 3 meses preciosa. Muy sociable con todos. Perfecta para familias con niños. Necesita espacio y ejercicio diario.',                                                  1, 0, 1, 1, 'Sevilla',   'Dos Hermanas',  'PENDIENTE_APROBACION', 6);

-- Gatos
INSERT INTO animales (nombre, especie, raza, sexo, etapa_vida, tamanyo, color, descripcion, vacunado, esterilizado, desparasitado, microchip, provincia, localidad, estado, motivo_rechazo, publicador_id) VALUES
('Luna',  'Gato', 'Europeo Común', 'HEMBRA', 'CACHORRO', 'PEQUENO', 'NARANJA',  'Gatita de 3 meses muy juguetona y cariñosa. Está acostumbrada a vivir en piso y se lleva bien con otros animales. Busca un hogar tranquilo.',                               1, 1, 1, 1, 'Madrid',   'Alcalá de Henares', 'DISPONIBLE',          NULL, 3),
('Nala',  'Gato', 'Siamés',        'HEMBRA', 'ADULTO',   'PEQUENO', 'BICOLOR',  'Gata siamesa de 4 años muy elegante y tranquila. Le gusta la calma y los mimos. No es muy sociable con otros gatos pero adora a las personas.',                              1, 1, 1, 0, 'Valencia', 'Valencia',           'DISPONIBLE',          NULL, 5),
('Mochi', 'Gato', 'Maine Coon',    'MACHO',  'ADULTO',   'GRANDE',  'GRIS',     'Maine Coon de 2 años espectacular. Muy sociable, inteligente y juguetón. Se lleva bien con niños y con perros. Necesita espacio y estimulación mental.',                     1, 1, 1, 1, 'Granada',  'Granada',            'DISPONIBLE',          NULL, 10),
('Simba', 'Gato', 'Persa',         'MACHO',  'CACHORRO', 'PEQUENO', 'DORADO',   'Gatito persa de 2 meses con un pelaje precioso. Muy tranquilo y afectuoso. Requiere cepillado diario.',                                                                      1, 0, 1, 0, 'Madrid',   'Madrid',             'PENDIENTE_APROBACION', NULL, 3),
('Coco',  'Gato', 'Europeo Común', 'MACHO',  'MAYOR',    'MEDIANO', 'NEGRO',    'Gato mayor buscando hogar.',                                                                                                                                                   1, 1, 1, 1, 'Alicante', 'Benidorm',           'RECHAZADO',           'La descripción es demasiado corta. Por favor amplíala con más información sobre el animal.', 11);

-- Conejos
INSERT INTO animales (nombre, especie, raza, sexo, etapa_vida, tamanyo, color, descripcion, vacunado, esterilizado, desparasitado, microchip, provincia, localidad, estado, publicador_id) VALUES
('Canela', 'Conejo', 'Rex',    'HEMBRA', 'ADULTO',   'MEDIANO', 'MARRON', 'Coneja Rex de 2 años muy cariñosa y activa. Le encanta explorar y necesita espacio para correr. Está acostumbrada a vivir en interior con supervisión.',  1, 1, 0, 0, 'Málaga',   'Marbella',   'DISPONIBLE', 7),
('Perla',  'Conejo', 'Angora', 'HEMBRA', 'CACHORRO', 'PEQUENO', 'BLANCO', 'Conejita angora de 3 meses muy tierna. Su pelo requiere cepillado frecuente. Ideal para personas que buscan un animal tranquilo y bonito.',               1, 0, 0, 0, 'Alicante', 'Benidorm',   'DISPONIBLE', 11);

-- Aves
INSERT INTO animales (nombre, especie, raza, sexo, etapa_vida, tamanyo, color, descripcion, vacunado, esterilizado, desparasitado, microchip, provincia, localidad, estado, publicador_id) VALUES
('Pico', 'Ave', 'Periquito',  'MACHO',  'ADULTO', 'PEQUENO', 'BICOLOR', 'Periquito de 1 año muy parlanchín y alegre. Ya dice algunas palabras. Necesita compañía y una jaula amplia.', 0, 0, 0, 0, 'Zaragoza', 'Zaragoza', 'DISPONIBLE', 8),
('Lora', 'Ave', 'Loro Gris',  'HEMBRA', 'ADULTO', 'MEDIANO', 'GRIS',    'Loro gris africano de 5 años con gran vocabulario. Muy inteligente pero requiere dedicación y estimulación diaria.', 0, 0, 0, 0, 'Valencia', 'Valencia', 'DISPONIBLE', 5);

-- Hámsters
INSERT INTO animales (nombre, especie, raza, sexo, etapa_vida, tamanyo, color, descripcion, vacunado, esterilizado, desparasitado, microchip, provincia, localidad, estado, publicador_id) VALUES
('Bolita', 'Hamster', 'Sirio', 'HEMBRA', 'CACHORRO', 'PEQUENO', 'DORADO', 'Hámster siria de 2 meses muy activa y curiosa. Viene con su jaula y rueda. Perfecta para iniciarse en el mundo de las mascotas.',   0, 0, 0, 0, 'Madrid',    'Madrid',   'DISPONIBLE', 3),
('Pipas',  'Hamster', 'Ruso',  'MACHO',  'ADULTO',   'PEQUENO', 'GRIS',   'Hámster ruso de 8 meses muy tranquilo y fácil de manejar. Está acostumbrado al contacto humano desde pequeño.',                      0, 0, 0, 0, 'Barcelona', 'Badalona', 'DISPONIBLE', 4);

-- Tortugas
INSERT INTO animales (nombre, especie, raza, sexo, etapa_vida, tamanyo, color, descripcion, vacunado, esterilizado, desparasitado, microchip, provincia, localidad, estado, publicador_id) VALUES
('Turbo', 'Tortuga', 'Mediterránea', 'MACHO',  'MAYOR',  'PEQUENO', 'MARRON', 'Tortuga mediterránea de más de 20 años. Muy resistente y fácil de cuidar. Necesita jardín o terrario amplio con luz UVB.',      0, 0, 0, 0, 'Sevilla', 'Dos Hermanas', 'DISPONIBLE', 6),
('Lenta', 'Tortuga', 'Rusa',         'HEMBRA', 'ADULTO', 'PEQUENO', 'MARRON', 'Tortuga rusa de 10 años muy tranquila. Hiberna en invierno. Ideal para personas que buscan una mascota de bajo mantenimiento.', 0, 0, 0, 0, 'Murcia',  'Cartagena',    'DISPONIBLE', 12);


-- ─────────────────────────────────────────────────────────────────────────────
-- FAVORITOS
-- Los IDs de animales siguen el orden de inserción:
-- 1=Rocky, 2=Toby, 3=Bolt, 4=Zeus, 5=Kira,
-- 6=Luna, 7=Nala, 8=Mochi, 9=Simba, 10=Coco,
-- 11=Canela, 12=Perla, 13=Pico, 14=Lora,
-- 15=Bolita, 16=Pipas, 17=Turbo, 18=Lenta
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO favoritos (usuario_id, animal_id) VALUES
(4,  6),   -- Carlos   -> Luna
(4,  2),   -- Carlos   -> Toby
(5,  1),   -- Ana      -> Rocky
(5,  3),   -- Ana      -> Bolt
(6,  7),   -- Miguel   -> Nala
(7,  11),  -- Sara     -> Canela
(7,  8),   -- Sara     -> Mochi
(8,  12),  -- Pedro    -> Perla
(9,  3),   -- Marta    -> Bolt
(10, 6),   -- Javier   -> Luna
(11, 1),   -- Isabel   -> Rocky
(12, 4);   -- Roberto  -> Zeus


-- ─────────────────────────────────────────────────────────────────────────────
-- SOLICITUDES DE ADOPCIÓN
-- Adaptadas desde el antiguo sistema de citas.
-- Los estados: ACEPTADA, PENDIENTE, RECHAZADA se mantienen igual.
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO solicitudes_adopcion (animal_id, solicitante_id, publicador_id, mensaje, estado) VALUES
(1,  5,  4,  'Hola Carlos, me encantaría adoptar a Rocky. Tengo jardín y experiencia con labradores.',                                                'ACEPTADA'),
(6,  6,  3,  'Hola Laura, estoy muy interesado en Luna. Vivo solo y busco compañía. Tengo el piso preparado para un gato.',                           'PENDIENTE'),
(7,  7,  5,  'Hola Ana, me gustaría adoptar a Nala. Tengo experiencia con gatos siameses y le daría mucho cariño.',                                   'RECHAZADA'),
(3,  9,  8,  'Hola Marta, me ha emocionado la historia de Bolt. Tengo sofá y tiempo libre de sobra para darle la vida que merece.',                   'PENDIENTE'),
(11, 12, 7,  'Hola Sara, me interesa Canela. Tengo terraza amplia y ya tuve conejos antes. Puedo acercarme cuando quieras.',                          'ACEPTADA'),
(8,  11, 9,  'Hola Javier, Mochi es precioso. Tengo una casa grande y mucho tiempo para dedicarle. ¿Podemos hablar para concretar algo?',             'PENDIENTE');
