-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS sistema_ventas CHARACTER SET utf8 COLLATE utf8_general_ci;
USE sistema_ventas;

-- Crear tabla de categorías
CREATE TABLE IF NOT EXISTS categorias (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insertar categorías iniciales
INSERT INTO categorias (id, nombre) VALUES 
(1, 'Big Cola'),
(2, 'Otros Productos');

-- Crear tabla de productos
CREATE TABLE IF NOT EXISTS productos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    categoria_id INT(11) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Crear tabla de rutas
CREATE TABLE IF NOT EXISTS rutas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    es_big_cola TINYINT(1) NOT NULL DEFAULT 0,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insertar rutas iniciales
INSERT INTO rutas (id, nombre, es_big_cola) VALUES 
(1, 'Ruta 1', 0),
(2, 'Ruta 2', 0),
(3, 'Ruta 3', 0),
(4, 'Ruta 4', 0),
(5, 'Ruta Big Cola', 1),
(6, 'Tienda Local', 0);

-- Crear tabla de ventas
CREATE TABLE IF NOT EXISTS ventas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    fecha DATE NOT NULL,
    ruta_id INT(11) NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Crear tabla de detalle de ventas
CREATE TABLE IF NOT EXISTS detalle_ventas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    venta_id INT(11) NOT NULL,
    producto_id INT(11) NOT NULL,
    cantidad INT(11) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insertar productos de Big Cola
INSERT INTO productos (nombre, precio, categoria_id) VALUES
("Big Cola 300ml (24 Pack)", 5.00, 1),
("Big Cola 360ml (24 Pack)", 6.00, 1),
("Big Lima Limón 360ml (12 Pack)", 3.00, 1),
("Big Naranja 360ml (12 Pack)", 3.00, 1),
("Big Roja 360ml (12 Pack)", 3.00, 1),
("Big Uva 360ml (12 Pack)", 3.00, 1),
("Big Cola 625ml (12 Pack)", 4.55, 1),
("Big Cola 1L (12 Pack)", 6.00, 1),
("Big Cola 1.3L (8 Pack)", 5.00, 1),
("Big Cola 1.8L (6 Pack)", 5.00, 1),
("Big Cola 2.6L (6 Pack)", 6.25, 1),
("Big Cola 3.03L (6 Pack)", 7.50, 1),
("Big Lima Limón 3.03L (6 Pack)", 7.50, 1),
("Big Naranja 3.03L (6 Pack)", 7.50, 1),
("Big Uva 3.03L (6 Pack)", 7.50, 1),
("Kola Real K.R 300ml (12 Pack)", 2.50, 1),
("Volt Yellow 300ml (12 Pack)", 4.00, 1),
("Cifrut Citrus Punch Naranja 250ml (24 Pack)", 5.00, 1),
("Cifrut Fruit Punch Rojo 250ml (24 Pack)", 5.00, 1),
("Pulp Manzana Caja 250ml (12 pack)", 3.40, 1),
("Pulp Melocotón Caja 145ml (12 Pack)", 2.50, 1),
("Agua \"Cielo\" 375ml (24 Pack)", 4.00, 1),
("Bio Aloe \"Aloe y Uva\" 360ml (6 Pack)", 3.75, 1),
("Cifrut Citrus Punch Naranja 360ml (12 Pack)", 3.00, 1),
("Cifrut Fruit Punch Rojo 360ml (12 Pack)", 3.00, 1),
("Sporade Blue Berry 360ml (12 Pack)", 3.80, 1),
("Sporade Fruit Punch 360ml (12 Pack)", 3.80, 1),
("Sporade Uva 360ml (12 Pack)", 3.80, 1),
("Volt Go 360ml (12 Pack)", 3.55, 1),
("Naturaloe Aloe Vera 500ml (24 Pack)", 24.00, 1),
("Vita Aloe 500ml Caja (24 Pack)", 24.00, 1),
("Agua \"Cielo\" 625ml (20 Pack)", 4.50, 1),
("Cifrut Citrus Punch Naranja 625ml (15 Pack)", 5.70, 1),
("Sporade 625ml (12 Pack)", 1.00, 1),
("Sporade Blue Berry 625ml (12 Pack)", 5.75, 1),
("Sporade Fruit Punch 625ml (12 Pack)", 5.75, 1),
("Volt Ponche de Frutas 625ml (12 Pack)", 8.00, 1),
("Volt Yellow Lata 473ml (6 Pack)", 5.00, 1),
("Agua \"Cielo\" 1L (8 Pack)", 3.10, 1),
("Cifrut Citrus Punch 1.3L (8 Pack)", 5.00, 1),
("Cifrut Fruit Punch Rojo 1.3L (8 Pack)", 5.00, 1),
("Cifrut Citrus Punch Naranja 2.6L (6 Pack)", 6.25, 1),
("Cifrut Citrus Punch Naranja 3.03L (6 Pack)", 7.50, 1),
("Cifrut Fruit Punch Rojo 3.03L (6 Pack)", 7.50, 1),
("Coca-Cola lata 354ml (24 Pack)", 14.45, 1),
("Fanta Naranja Lata 354ml (12 Pack)", 7.22, 1),
("Sprite 354ml (12 Pack)", 7.22, 1),
("Tropical Fresa Lata 354ml (12 Pack)", 7.22, 1),
("Tropical Uva Lata 354ml (12 Pack)", 7.22, 1);

-- Insertar otros productos
INSERT INTO productos (nombre, precio, categoria_id) VALUES
("Agua \"Aqua\" 750ml (12 Pack)", 3.50, 2),
("AMP Energy 600ml (12 Pack)", 10.00, 2),
("Aceite Vegetal El Dorado 750ml (Unidad)", 1.85, 2),
("Agua \"Caída del Cielo\" (Fardo)", 0.83, 2),
("Agua \"De Los Ángeles\" Garrafa 19L (Unidad)", 2.00, 2),
("Baygon Oko Mosquitos Y Moscas (Unidad)", 2.25, 2),
("Baygon Poder Mortal (Unidad)", 2.25, 2),
("Big Cola Lata 355ml (6 Pack)", 2.50, 2),
("Café Instantáneo Aroma Caja (50 Sobres)", 3.00, 2),
("Café Instantáneo Coscafe Caja (40 sobres)", 2.85, 2),
("Café Instantáneo Coscafe Caja (50+5 Sobres)", 3.95, 2),
("Café Instantáneo Riko Dispensador Caja (50 Sobres)", 3.50, 2),
("Coca-Cola 1.25L (12 Pack)", 13.50, 2),
("Coca-Cola 3L (4 Pack)", 8.50, 2),
("Coca-Cola Vidrio 1.25L (12 Pack)", 10.25, 2),
("Coca-Cola Vidrio 354ml (24 Pack)", 10.25, 2),
("Del Valle Mandarina 1.5L (6 Pack)", 5.85, 2),
("Del Valle Mandarina 2.5L (6 Pack)", 8.55, 2),
("Del Valle Mandarina 500ml (12 Pack)", 5.25, 2),
("Fanta Naranja 1.25L (6 Pack)", 13.50, 2),
("Fanta Naranja 2.5L (6 Pack)", 11.55, 2),
("Fanta Vidrio 354ml (24 Pack)", 10.25, 2),
("Fresca 2.5L (6 Pack)", 11.55, 2),
("Frucci Frutti Fresh 200ml (24 Pack)", 3.25, 2),
("Frutado Surtido 355ml (12 Pack)", 3.00, 2),
("Frutado de Manzana 355ml (12 Pack)", 3.00, 2),
("Frutado de Pera 355ml (12 Pack)", 3.00, 2),
("Frutti Fresh 200ml (12 Pack)", 5.00, 2),
("Gatorade Celeste 600ml (24 pack)", 20.10, 2),
("Gatorade Limón 600ml (24 Pack)", 20.10, 2),
("Gatorade Naranja 600ml (24 Pack)", 20.10, 2),
("Gatorade Rojo 600ml (24 Pack)", 20.10, 2),
("Gatorade Surtido 600ml (24 Pack)", 20.10, 2),
("Gatorade Uva 600ml (24 Pack)", 20.10, 2),
("Golden Vidrio 330ml (24 Pack)", 22.00, 2),
("Néctar California Surtido 340ml (24 Pack)", 10.00, 2),
("Néctar California de Durazno 330ml (24 Pack)", 10.00, 2),
("Néctar California de Manzana 330ml (24 Pack)", 10.00, 2),
("Néctar California de Pera 330ml (24 Pack)", 10.00, 2),
("Papel Higiénico Nevax Fardo (12 Pack)", 9.60, 2),
("Pepsi 1.5L (12 Pack)", 11.25, 2),
("Petit Durazno 330ml (24 Pack)", 13.00, 2),
("Petit Lata Surtido 330ml (24 Pack)", 13.00, 2),
("Petit Manzana 330ml (24 Pack)", 13.00, 2),
("Petit Piña 330ml (24 Pack)", 13.00, 2),
("Petit Tetra Surtido Caja 200ml (24 Pack)", 7.25, 2),
("Pilsener Lata 12onz (24 Pack)", 24.00, 2),
("Pilsener Lata 16onz (24 Pack)", 29.00, 2),
("Pilsener Vidrio 330ml (24 Pack)", 22.00, 2),
("Powerade Avalancha 500ml (12 Pack)", 7.25, 2),
("Powerade Avalancha 750ml (12 Pack)", 9.25, 2),
("Quanty Naranja 237ml (24 Pack)", 5.00, 2),
("Quanty Ponche de Frutas 237ml (24 Pack)", 5.00, 2),
("Quanty Uva 237ml (24 Pack)", 5.00, 2),
("Raptor 300ml (24 Pack)", 10.00, 2),
("Raptor 600ml (12 Pack)", 10.00, 2),
("Salutaris Agua Mineral 355ml (24 Pack)", 13.00, 2),
("Salutaris Naranja 355ml (24 Pack)", 13.00, 2),
("Salutaris Surtido 355ml (24 Pack)", 13.00, 2),
("Salutaris de Limón 355ml (24 Pack)", 13.00, 2),
("Surf Junior Mandarina 400ml Bolsa (12 Pack)", 2.50, 2),
("Surf Junior Naranja Bolsa 400ml (12 Pack)", 2.50, 2),
("Tropical Uva 2.5L (6 Pack)", 11.55, 2),
("Tukyy 240ml (12 Pack)", 2.50, 2);