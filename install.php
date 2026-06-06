<?php

/**
 * PAWly - Script de instalación
 * Ejecutar UNA sola vez desde el navegador:
 *   http://localhost/pawly-backend/install.php
 * O desde la terminal:
 *   php install.php
 *
 * Crea la base de datos, las tablas, el usuario MySQL e inserta los datos.
 */

// ─── Configuración ────────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'pawly_db');
define('DB_USER', 'pawly');
define('DB_PASS', 'pawly123');
define('DB_ROOT', 'root');   // usuario root de MySQL (XAMPP por defecto no tiene contraseña)
define('DB_ROOT_PASS', '');      // contraseña root (vacía en XAMPP por defecto)

$isCli = php_sapi_name() === 'cli';

function out(string $msg, bool $ok = true): void
{
    $isCli = php_sapi_name() === 'cli';
    $icon = $ok ? '✅' : '❌';
    if ($isCli) {
        echo ($ok ? '[OK] ' : '[ERR] ') . $msg . PHP_EOL;
    } else {
        $color = $ok ? '#2d6a4f' : '#c8613a';
        echo "<p style='font-family:monospace;color:{$color}'>{$icon} {$msg}</p>";
        ob_flush();
        flush();
    }
}

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'>
    <title>PAWly Install</title>
    <style>body{background:#fdf6ee;padding:40px;max-width:700px;margin:0 auto}
    h1{font-family:Georgia,serif;color:#1e1a16}
    p{margin:4px 0;font-size:.95rem}</style></head><body>
    <h1>🐾 PAWly — Instalación</h1>";
    ob_flush();
    flush();
}

// ─── 1. Conectar como root ────────────────────────────────────────────────────
try {
    $root = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_ROOT,
        DB_ROOT_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    out("Conexión root a MySQL correcta");
} catch (PDOException $e) {
    out("No se pudo conectar como root: " . $e->getMessage(), false);
    exit(1);
}

// ─── 2. Crear base de datos ───────────────────────────────────────────────────
$root->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
             CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
out("Base de datos '" . DB_NAME . "' lista");

// ─── 3. Crear usuario MySQL ───────────────────────────────────────────────────
try {
    $root->exec("CREATE USER IF NOT EXISTS '" . DB_USER . "'@'localhost'
                 IDENTIFIED BY '" . DB_PASS . "'");
    $root->exec("GRANT ALL PRIVILEGES ON `" . DB_NAME . "`.* TO '" . DB_USER . "'@'localhost'");
    $root->exec("FLUSH PRIVILEGES");
    out("Usuario MySQL '" . DB_USER . "' creado y con permisos");
} catch (PDOException $e) {
    out("Aviso al crear usuario (puede que ya exista): " . $e->getMessage());
}

// ─── 4. Conectar a pawly_db ───────────────────────────────────────────────────
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_ROOT,
        DB_ROOT_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    out("Conexión a '" . DB_NAME . "' correcta");
} catch (PDOException $e) {
    out("No se pudo conectar a la BD: " . $e->getMessage(), false);
    exit(1);
}

// ─── 5. Crear tablas ─────────────────────────────────────────────────────────
$tablas = [

    "usuarios" => "CREATE TABLE IF NOT EXISTS usuarios (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "animales" => "CREATE TABLE IF NOT EXISTS animales (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "favoritos" => "CREATE TABLE IF NOT EXISTS favoritos (
    usuario_id     INT UNSIGNED NOT NULL,
    animal_id      INT UNSIGNED NOT NULL,
    fecha_guardado TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, animal_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (animal_id)  REFERENCES animales(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "solicitudes_adopcion" => "CREATE TABLE IF NOT EXISTS solicitudes_adopcion (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

];

foreach ($tablas as $nombre => $sql) {
    $db->exec($sql);
    out("Tabla '$nombre' creada");
}

// ─── 6. Comprobar si ya hay datos ────────────────────────────────────────────
$total = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
if ($total > 0) {
    out("Ya hay datos en la BD, omitiendo inserción de datos de prueba");
    finalizar($isCli);
    exit(0);
}

// ─── 7. Insertar usuarios ────────────────────────────────────────────────────
$passAdmin = password_hash('admin123', PASSWORD_BCRYPT);
$passUser = password_hash('password123', PASSWORD_BCRYPT);

$usuarios = [
    ['Admin PAWly', 'admin@pawly.com', $passAdmin, '600000000', 'Madrid', 'Madrid', 'ADMIN'],
    ['lgarlaz', 'lgarlaz@pawly.com', $passAdmin, '600000001', 'Barcelona', 'Barcelona', 'ADMIN'],
    ['Laura Martínez', 'laura@pawly.com', $passUser, '611222333', 'Madrid', 'Alcalá de Henares', 'USER'],
    ['Carlos Ruiz', 'carlos@pawly.com', $passUser, '622333444', 'Barcelona', 'Badalona', 'USER'],
    ['Ana García', 'ana@pawly.com', $passUser, '633444555', 'Valencia', 'Valencia', 'USER'],
    ['Miguel Torres', 'miguel@pawly.com', $passUser, '644555666', 'Sevilla', 'Dos Hermanas', 'USER'],
    ['Sara López', 'sara@pawly.com', $passUser, '655666777', 'Málaga', 'Marbella', 'USER'],
    ['Pedro Sánchez', 'pedro@pawly.com', $passUser, '666777888', 'Zaragoza', 'Zaragoza', 'USER'],
    ['Marta Fernández', 'marta@pawly.com', $passUser, '677888999', 'Bilbao', 'Bilbao', 'USER'],
    ['Javier Moreno', 'javier@pawly.com', $passUser, '688999000', 'Granada', 'Granada', 'USER'],
    ['Isabel Díaz', 'isabel@pawly.com', $passUser, '699000111', 'Alicante', 'Benidorm', 'USER'],
    ['Roberto Jiménez', 'roberto@pawly.com', $passUser, '600111222', 'Murcia', 'Cartagena', 'USER'],
];

$stmtU = $db->prepare(
    "INSERT INTO usuarios (nombre, email, password, telefono, provincia, localidad, rol)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
foreach ($usuarios as $u) {
    $stmtU->execute($u);
}
out("12 usuarios insertados (contraseñas hasheadas con bcrypt)");

// ─── 8. Insertar animales ────────────────────────────────────────────────────
// [nombre, especie, raza, sexo, etapa_vida, tamanyo, color, descripcion,
//  vacunado, esterilizado, desparasitado, microchip,
//  provincia, localidad, estado, motivo_rechazo, publicador_id]
$animales = [
    // Perros
    ['Rocky', 'Perro', 'Labrador', 'MACHO', 'ADULTO', 'GRANDE', 'DORADO', 'Labrador de 3 años muy equilibrado y obediente. Sabe los comandos básicos y le encanta el agua. Necesita salidas diarias. Ideal para familias activas.', 1, 1, 1, 1, 'Barcelona', 'Badalona', 'DISPONIBLE', null, 4],
    ['Toby', 'Perro', 'Beagle', 'MACHO', 'CACHORRO', 'MEDIANO', 'TRICOLOR', 'Cachorro de 4 meses lleno de energía. Muy inteligente y fácil de educar. Le encanta jugar con pelotas y explorar el jardín.', 1, 0, 1, 1, 'Sevilla', 'Dos Hermanas', 'DISPONIBLE', null, 6],
    ['Bolt', 'Perro', 'Mestizo', 'MACHO', 'MAYOR', 'MEDIANO', 'NEGRO', 'Perro de 9 años rescatado de la calle. Muy tranquilo y agradecido. Solo quiere un sofá calentito y una familia que lo quiera.', 1, 1, 1, 1, 'Bilbao', 'Bilbao', 'DISPONIBLE', null, 9],
    ['Zeus', 'Perro', 'Pastor Alemán', 'MACHO', 'ADULTO', 'GRANDE', 'NEGRO', 'Pastor alemán de 5 años con carácter fuerte pero muy leal. Ha recibido adiestramiento básico. Necesita dueño con experiencia.', 1, 1, 1, 1, 'Murcia', 'Cartagena', 'DISPONIBLE', null, 12],
    ['Kira', 'Perro', 'Golden Retriever', 'HEMBRA', 'CACHORRO', 'GRANDE', 'DORADO', 'Golden de 3 meses preciosa. Muy sociable con todos. Perfecta para familias con niños. Necesita espacio y ejercicio diario.', 1, 0, 1, 1, 'Sevilla', 'Dos Hermanas', 'PENDIENTE_APROBACION', null, 6],
    // Gatos
    ['Luna', 'Gato', 'Europeo Común', 'HEMBRA', 'CACHORRO', 'PEQUENO', 'NARANJA', 'Gatita de 3 meses muy juguetona y cariñosa. Está acostumbrada a vivir en piso y se lleva bien con otros animales. Busca un hogar tranquilo.', 1, 1, 1, 1, 'Madrid', 'Alcalá de Henares', 'DISPONIBLE', null, 3],
    ['Nala', 'Gato', 'Siamés', 'HEMBRA', 'ADULTO', 'PEQUENO', 'BICOLOR', 'Gata siamesa de 4 años muy elegante y tranquila. Le gusta la calma y los mimos. No es muy sociable con otros gatos pero adora a las personas.', 1, 1, 1, 0, 'Valencia', 'Valencia', 'DISPONIBLE', null, 5],
    ['Mochi', 'Gato', 'Maine Coon', 'MACHO', 'ADULTO', 'GRANDE', 'GRIS', 'Maine Coon de 2 años espectacular. Muy sociable, inteligente y juguetón. Se lleva bien con niños y con perros. Necesita espacio y estimulación mental.', 1, 1, 1, 1, 'Granada', 'Granada', 'DISPONIBLE', null, 10],
    ['Simba', 'Gato', 'Persa', 'MACHO', 'CACHORRO', 'PEQUENO', 'DORADO', 'Gatito persa de 2 meses con un pelaje precioso. Muy tranquilo y afectuoso. Requiere cepillado diario.', 1, 0, 1, 0, 'Madrid', 'Madrid', 'PENDIENTE_APROBACION', null, 3],
    ['Coco', 'Gato', 'Europeo Común', 'MACHO', 'MAYOR', 'MEDIANO', 'NEGRO', 'Gato mayor buscando hogar.', 1, 1, 1, 1, 'Alicante', 'Benidorm', 'RECHAZADO', 'La descripción es demasiado corta. Por favor amplíala con más información sobre el animal.', 11],
    // Conejos
    ['Canela', 'Conejo', 'Rex', 'HEMBRA', 'ADULTO', 'MEDIANO', 'MARRON', 'Coneja Rex de 2 años muy cariñosa y activa. Le encanta explorar y necesita espacio para correr. Está acostumbrada a vivir en interior con supervisión.', 1, 1, 0, 0, 'Málaga', 'Marbella', 'DISPONIBLE', null, 7],
    ['Perla', 'Conejo', 'Angora', 'HEMBRA', 'CACHORRO', 'PEQUENO', 'BLANCO', 'Conejita angora de 3 meses muy tierna. Su pelo requiere cepillado frecuente. Ideal para personas que buscan un animal tranquilo y bonito.', 1, 0, 0, 0, 'Alicante', 'Benidorm', 'DISPONIBLE', null, 11],
    // Aves
    ['Pico', 'Ave', 'Periquito', 'MACHO', 'ADULTO', 'PEQUENO', 'BICOLOR', 'Periquito de 1 año muy parlanchín y alegre. Ya dice algunas palabras. Necesita compañía y una jaula amplia.', 0, 0, 0, 0, 'Zaragoza', 'Zaragoza', 'DISPONIBLE', null, 8],
    ['Lora', 'Ave', 'Loro Gris', 'HEMBRA', 'ADULTO', 'MEDIANO', 'GRIS', 'Loro gris africano de 5 años con gran vocabulario. Muy inteligente pero requiere dedicación y estimulación diaria.', 0, 0, 0, 0, 'Valencia', 'Valencia', 'DISPONIBLE', null, 5],
    // Hámsters
    ['Bolita', 'Hamster', 'Sirio', 'HEMBRA', 'CACHORRO', 'PEQUENO', 'DORADO', 'Hámster siria de 2 meses muy activa y curiosa. Viene con su jaula y rueda. Perfecta para iniciarse en el mundo de las mascotas.', 0, 0, 0, 0, 'Madrid', 'Madrid', 'DISPONIBLE', null, 3],
    ['Pipas', 'Hamster', 'Ruso', 'MACHO', 'ADULTO', 'PEQUENO', 'GRIS', 'Hámster ruso de 8 meses muy tranquilo y fácil de manejar. Está acostumbrado al contacto humano desde pequeño.', 0, 0, 0, 0, 'Barcelona', 'Badalona', 'DISPONIBLE', null, 4],
    // Tortugas
    ['Turbo', 'Tortuga', 'Mediterránea', 'MACHO', 'MAYOR', 'PEQUENO', 'MARRON', 'Tortuga mediterránea de más de 20 años. Muy resistente y fácil de cuidar. Necesita jardín o terrario amplio con luz UVB.', 0, 0, 0, 0, 'Sevilla', 'Dos Hermanas', 'DISPONIBLE', null, 6],
    ['Lenta', 'Tortuga', 'Rusa', 'HEMBRA', 'ADULTO', 'PEQUENO', 'MARRON', 'Tortuga rusa de 10 años muy tranquila. Hiberna en invierno. Ideal para personas que buscan una mascota de bajo mantenimiento.', 0, 0, 0, 0, 'Murcia', 'Cartagena', 'DISPONIBLE', null, 12],
];

$stmtA = $db->prepare(
    "INSERT INTO animales
        (nombre, especie, raza, sexo, etapa_vida, tamanyo, color, descripcion,
         vacunado, esterilizado, desparasitado, microchip,
         provincia, localidad, estado, motivo_rechazo, publicador_id)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
);
foreach ($animales as $a) {
    $stmtA->execute($a);
}
out("18 animales insertados");

// ─── 9. Insertar favoritos ───────────────────────────────────────────────────
// usuario_id -> animal_id (por posición de inserción: 1=Rocky...6=Luna...8=Mochi...)
$favoritos = [
    [4, 6],
    [4, 2],
    [5, 1],
    [5, 3],
    [6, 7],
    [7, 11],
    [7, 8],
    [8, 12],
    [9, 3],
    [10, 6],
    [11, 1],
    [12, 4]
];
$stmtF = $db->prepare("INSERT INTO favoritos (usuario_id, animal_id) VALUES (?,?)");
foreach ($favoritos as $f) {
    $stmtF->execute($f);
}
out("12 favoritos insertados");

// ─── 10. Insertar solicitudes ────────────────────────────────────────────────
$solicitudes = [
    [1, 5, 4, 'Hola Carlos, me encantaría adoptar a Rocky. Tengo jardín y experiencia con labradores.', 'ACEPTADA'],
    [6, 6, 3, 'Hola Laura, estoy muy interesado en Luna. Vivo solo y busco compañía. Tengo el piso preparado para un gato.', 'PENDIENTE'],
    [7, 7, 5, 'Hola Ana, me gustaría adoptar a Nala. Tengo experiencia con gatos siameses y le daría mucho cariño.', 'RECHAZADA'],
    [3, 9, 8, 'Hola Marta, me ha emocionado la historia de Bolt. Tengo sofá y tiempo libre de sobra para darle la vida que merece.', 'PENDIENTE'],
    [11, 12, 7, 'Hola Sara, me interesa Canela. Tengo terraza amplia y ya tuve conejos antes. Puedo acercarme cuando quieras.', 'ACEPTADA'],
    [8, 11, 9, 'Hola Javier, Mochi es precioso. Tengo una casa grande y mucho tiempo para dedicarle. ¿Podemos hablar para concretar algo?', 'PENDIENTE'],
];
$stmtS = $db->prepare(
    "INSERT INTO solicitudes_adopcion (animal_id, solicitante_id, publicador_id, mensaje, estado)
     VALUES (?,?,?,?,?)"
);
foreach ($solicitudes as $s) {
    $stmtS->execute($s);
}
out("6 solicitudes de adopción insertadas");

// ─── 11. Sincronizar animales adoptados ──────────────────────────────────────
$db->exec(
    "UPDATE animales a
     INNER JOIN solicitudes_adopcion s ON s.animal_id = a.id
     SET a.estado = 'ADOPTADO'
     WHERE s.estado = 'ACEPTADA'"
);
out("Animales con solicitud aceptada marcados como ADOPTADOS");

// ─── Fin ─────────────────────────────────────────────────────────────────────
function finalizar(bool $isCli): void
{
    if ($isCli) {
        echo PHP_EOL . "Instalación completada. Elimina install.php antes de subir a producción." . PHP_EOL;
    } else {
        echo "<br><p style='font-family:Georgia,serif;font-size:1.1rem;color:#2d6a4f'>
              🎉 Instalación completada.<br>
              <strong>Elimina o renombra <code>install.php</code> antes de usar en producción.</strong>
              </p></body></html>";
    }
}

finalizar($isCli);
