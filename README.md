# TecnoStock

TecnoStock es un sistema web de inventario para una tienda de accesorios tecnológicos. En el caso original, la tienda llevaba el stock en una planilla y tenía problemas con códigos repetidos y cantidades incorrectas. Con este sistema, el encargado puede consultar el catálogo y registrar las entradas y salidas desde un mismo lugar.

## Cómo instalarlo en XAMPP

1. Descarga el proyecto y deja sus archivos en `C:\xampp\htdocs\tecnostock`. El archivo `index.php` debe quedar dentro de esa carpeta.
2. Abre XAMPP e inicia **Apache** y **MySQL**.
3. Entra a `http://localhost/phpmyadmin` y crea una base de datos llamada `tecnostock`.
4. Selecciona la base `tecnostock`, abre la pestaña **Importar** y carga el archivo `tecnostock.sql`.
5. Si tu instalación de MySQL usa otros datos de conexión, ajústalos en `conexion.php`.
6. Abre `http://localhost/tecnostock/` e inicia sesión con el correo y la contraseña de prueba indicados en la entrega.

Las categorías ya vienen cargadas. El inventario comienza sin productos activos para que puedas registrar los tuyos durante la prueba.

## Cómo usarlo

- **Inicio** muestra el total de productos activos y cuántos están por debajo de su stock mínimo.
- **Agregar Producto** permite registrar el código, nombre, descripción, categoría, precio, stock inicial y stock mínimo.
- **Ver Inventario** permite buscar por código, nombre o categoría. En la columna **Acciones** puedes editar, desactivar o registrar un movimiento.
- **Movimiento** permite registrar entradas y salidas. El stock se actualiza automáticamente y, si queda por debajo del mínimo, la fila se resalta en el inventario.

El sistema está hecho con PHP, PDO y MySQL. `tecnostock.sql` contiene las tablas y sus relaciones, y `diseno.css` contiene los estilos de la página.

## Pruebas realizadas

| Prueba | Resultado |
| --- | --- |
| Guardar dos productos con el mismo código | El sistema rechaza el código repetido. |
| Agregar o editar con stock de `-1`, `2.5` o `0` | Rechaza `-1` y `2.5`, y acepta `0`. |
| Registrar una entrada de 5 unidades con stock inicial de 0 | El stock sube a 5 y queda registrado el movimiento. |
| Registrar una salida de 1 unidad con stock de 5 y mínimo de 5 | El stock baja a 4 y la fila se resalta. |
| Intentar una salida de 5 unidades cuando quedan 4 | El sistema rechaza la salida y conserva las 4 unidades. |
| Guardar una edición que se abrió antes de registrar una entrada | El sistema avisa que el stock cambió y conserva la cantidad actualizada. |
| Desactivar un producto con movimientos | El producto desaparece del listado activo y sus movimientos se conservan. |
