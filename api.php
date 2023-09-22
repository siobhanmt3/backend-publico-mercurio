<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header("Content-type: application/json; charset=utf-8");

    //DATOS DE LA NUBE
    $servername = "*";
    $username = "*";
    $password = "*";
    $dbname = "*";

    $comando=$_GET["comando"];

    //ESTABLECER CONEXIÓN
    $conn = new mysqli($servername, $username, $password, $dbname);

    //VERIFICAR CONEXIÓN
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //INICIAR SESIÓN
    if ($comando == "login") {
        if (isset($_GET["usuario"])) //isset comprueba que no sea null
            $usuario = $_GET["usuario"];
        else
            $usuario = "";
        if (isset($_GET["contrasena"]))
            $contrasena = $_GET["contrasena"];
        else
            $contrasena = "";
        if (isset($_GET["correo"]))
            $correo = $_GET["correo"];
        else
            $correo = "";

        //CONSULTA SQL
        $sql = "SELECT * FROM usuario where (correo='$correo' or usuario='$usuario')";
        $result = $conn->query($sql);
        $registros = array();
        $i = 0;

        if ($result->num_rows > 0) {
            // La contraseña coincide, se permite el inicio de sesión
            while ($row = $result->fetch_assoc()) {
                if (password_verify($contrasena, $row["contrasena"])) {
                    $registros[$i] = $row;
                    $i++;
                }
            }
            echo '{"records":' . json_encode($registros) . '}';
        } else {
            echo '{"records":[]}';
        }
    }
    //FIN


    //REGISTRAR USUARIOS
    if($comando == "signin"){

        //Registrar con estado
        /*
        Nota recordar que los estado estarán pre definidos desde la BD o localmente
        */
        $socialfb="";
        $socialig="";
        $descripcion="";
        $estado=$_GET["estado"];
        $foto="";

        $sql = "INSERT INTO dato (socialfb, socialig, descripcion, estado, foto)
        VALUES ('{$socialfb}','{$socialig}','{$descripcion}', '{$estado}', '{$foto}')";

        if ($conn->query($sql) === TRUE) {
            //echo 'ok';    //Eliminar esta línea
        } else {
            echo 'error';
        }

        $sql2 = "SELECT id FROM dato ORDER BY id DESC LIMIT 1";
        $result2 = $conn->query($sql2);

        if ($result2->num_rows > 0) {
            $registros=array();
            $i=0;
            while($row = $result2->fetch_assoc()) {
                $registros[$i]=$row;
                $i++;
                //echo "regis_____";  //Eliminar esta línea
                //echo implode(" ",$registros[0]);  //Eliminar esta línea
                $num = implode(" ",$registros[0]);
            }
        } else {
            //echo "[]";  //Eliminar esta línea
        }

        $usuario=$_GET["usuario"];
        $correo=$_GET["correo"];
        $contrasena=$_GET["contrasena"];

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario (usuario, correo, contrasena, id_dato)
                VALUES ('{$usuario}', '{$correo}', '{$hash}','{$num}')";

        if ($conn->query($sql) === TRUE) {
            $result = $conn->query("SELECT LAST_INSERT_ID()");
            $row = $result->fetch_row();
            $id_usuario = $row[0];
            $response = array('status' => 'ok', 'id_usuario' => $id_usuario);
        } else {
            $response = array('status' => 'Intenta de nuevo');
        }

        echo json_encode($response);

    }
    //FIN

    //EDITAR FOTO PERFIL DE USUARIO
    if ($comando == "editperfil") {
            $id = $_GET["id"];
            $sql = "SELECT id_dato FROM usuario WHERE id = '{$id}'";
            $resultado = $conn->query($sql);

            if ($resultado->num_rows > 0) {
                $registros=array();
                $i=0;
                while($row = $resultado->fetch_assoc()) {
                    $registros[$i]=$row;
                    $i++;
                    $id_dato = implode(" ", $registros[0]); // obtener el id de los datos del usuario
                }
            } else {
                echo "[]";
            }

            $foto = $_FILES['foto'];
            $foto_path = $foto['tmp_name'];
            $foto_type = $foto['type'];

            // Comprobar si la imagen es válida
            $valid_image_types = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($foto_type, $valid_image_types)) {
                echo '{"status": "Error: Tipo de imagen no válido"}';
                exit();
            }

            // Leer y codificar la imagen en base64
            $foto_base64 = base64_encode(file_get_contents($foto_path));

            $sql = "UPDATE dato SET foto='{$foto_base64}' WHERE id='{$id_dato}'";

            if ($conn->query($sql) === TRUE) {
                echo '{"status": "OK"}';
            } else {
                echo '{"status": "No se agregaron datos"}';
            }
    }
    //FIN


    //MOSTRAR LOS DATOS DEL USUARIO
    if ($comando == "userdata") {
        //Obtener los datos del usuario por medio del ID usuario
        $id = $_GET["id"];
        $sql = "SELECT id_dato FROM usuario WHERE id={$id}";
        $resultado = $conn->query($sql);

        if ($resultado->num_rows > 0) {
            $registros = array();
            $i = 0;
            while ($row = $resultado->fetch_assoc()) {
                $registros[$i] = $row;
                $i++;
                $id_dato = $row["id_dato"];
            }
        } else {
            echo "[]";
        }

        $sql = "SELECT * FROM dato WHERE id={$id_dato}";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $registros=array();
            while($row = $result->fetch_assoc()) {
                //$row["foto"] = base64_decode($row["foto"]); // convertir foto a base64getproduct
                $registros[] = $row;
            }
            
            $response = array('registros' => $registros);
            echo json_encode($response);

        } else {
            echo "[]";
        }
    }


    //EDITAR DATOS DE PERFIL DEL USUARIO
    if($comando == "editperfil2"){

        $id=$_GET["id"];
        $sql = "SELECT id_dato FROM usuario WHERE id = '{$id}'";
        $resultado = $conn->query($sql);

        if ($resultado->num_rows > 0) {
            $registros=array();
            $i=0;
            while($row = $resultado->fetch_assoc()) {
                $registros[$i]=$row;
                $i++;
                echo implode(" ",$registros[0]);
                $id_dato =implode(" ",$registros[0]);
                echo $id_dato;
            }
        } else {
            echo "[]";
        }

        $socialfb=$_GET["socialfb"];
        $socialig=$_GET["socialig"];
        $descripcion=$_GET["descripcion"];
        $estado=$_GET["estado"];


        $sql = "UPDATE dato SET socialfb='{$socialfb}', socialig='{$socialig}', descripcion='{$descripcion}', estado='{$estado}' WHERE id='{$id_dato}'";

        if ($conn->query($sql) === TRUE) {
            echo '{"Estatus": "SE HIZO"}';
        } else {
            echo '{"Estatus": "NO SE HIZO"}';
        }
    }
    //FIN

    
    //AGREGAR PRODUCTOS PARA REGALAR
    if($comando == "addproduct"){
        
        //Obtener el id del usuario
        $id=$_GET["id"];

        //Hacer el registro de un producto
        $nombre=$_GET["nombre"];
        $descripcion=$_GET["descripcion"];
        $categoria=$_GET["descripcion"];
        $existencia=$_GET["existencia"];
        $cantidad=$_GET["cantidad"];
        $foto = $_FILES['foto'];
        $foto_path = $foto['tmp_name'];
        $foto_type = $foto['type'];
        
        // Leer y codificar la imagen en base64
        $foto_base64 = base64_encode(file_get_contents($foto_path));
        
        //Agregar los datos que el usuario tiene del producto
        $sql = "INSERT INTO producto(id_usuario, nombre, descripcion, categoria, existencia, cantidad, foto) VALUES('{$id}','{$nombre}','{$descripcion}','{$categoria}','{$existencia}','{$cantidad}','{$foto_base64}')";
        //'{$foto_base64}'
            
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["Estatus" => "Guardado correctamente"]);
        } else {
            echo json_encode(["Estatus" => "Error al guardar datos: " . $conn->error]);
        }
        
    }
    // EDITAR PRODUCTO
    if($comando == "editproduct"){
        
        //Obtener el id del usuario
        $id_producto=$_GET["id"];

        //Hacer el registro de un producto
        $nombre=$_GET["nombre"];
        $descripcion=$_GET["descripcion"];
        $categoria=$_GET["descripcion"];
        $existencia=$_GET["existencia"];
        $cantidad=$_GET["cantidad"];
        $foto = $_FILES['foto'];
        $foto_path = $foto['tmp_name'];
        $foto_type = $foto['type'];
        
        // Leer y codificar la imagen en base64
        $foto_base64 = base64_encode(file_get_contents($foto_path));
        
        //Agregar los datos que el usuario tiene del producto
        $sql = "UPDATE producto 
        SET nombre = '{$nombre}', 
            descripcion = '{$descripcion}', 
            categoria = '{$categoria}', 
            existencia = '{$existencia}', 
            cantidad = '{$cantidad}', 
            foto = '{$foto_base64}' 
        WHERE id = {$id_producto}";
        //'{$foto_base64}'
            
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["Estatus" => "Guardado correctamente"]);
        } else {
            echo json_encode(["Estatus" => "Error al guardar datos: " . $conn->error]);
        }
    }
    //FIN
    
    //PRODUCTOS
    if($comando == "getproduct"){
        $idusuario=$_GET["idusuario"];
        $registros = array();
        $sql = "SELECT id FROM producto WHERE id_usuario = {$idusuario} ORDER BY id DESC LIMIT 1";
        $resultado = $conn->query($sql);
        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            $registros[0] = $fila;
            echo '{"records":' . json_encode($registros) . '}';
        } else {
            echo '{"records":' . "No se encontraron resultados" . '}';
        }
    }
  
    //DESACTIVAR O ACTIVAR NUEVAMENTE PRODUCTOS 'ENTREGADOS'
    if($comando == "delivered"){
        //Modificar la existencia de un producto por medio del id del producto
        $id = $_GET["id"];
        $existencia=$_GET["existencia"];
        
        $sql = "UPDATE producto SET existencia='{$existencia}' WHERE id='{$id}'";
    
        if ($conn->query($sql) === TRUE) {
            echo '{"Estatus": "SE HIZO"}';
        } else {
            echo '{"Estatus": "NO SE HIZO:c"}';
        }
    }

    //MIS PUBLICACIONES
    if ($comando == "selfpub") {
    //Se obtienen por medio del id del usuario
            $id = $_GET["id"];
            try {
                $sql = "SELECT p.*, f.foto FROM producto p
                            LEFT JOIN (
                                SELECT id_producto, foto
                                FROM fotoproducto
                                GROUP BY id_producto
                            ) f ON p.id = f.id_producto
                        WHERE p.id_usuario = {$id}";
                $result = $conn->query($sql);
        
                if ($result !== false) {
                    $registros = array();
                    $i = 0;
                    while ($row = $result->fetch_assoc()) {
                        $registros[$i] = $row;
                        $i++;
                    }
                    echo json_encode($registros);
                } else {
                    echo "[]";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
    }


    // MOSTRAR PRODUCTOS DEPENDIENDO EL ESTADO:)
    if ($comando == "home") {
        // Necesitamos el ID del usuario para excluir sus propios productos
        $id = $_GET["id"];
        $sql = "SELECT p.*
                FROM producto p
                LEFT JOIN usuario u ON u.id = p.id_usuario
                LEFT JOIN dato d ON d.id = u.id_dato
                WHERE u.id != '{$id}' AND d.estado = (
                    SELECT d2.estado FROM dato d2
                    INNER JOIN usuario u2 ON d2.id = u2.id_dato
                    WHERE u2.id = '{$id}'
                ) AND p.existencia = 1
                ORDER BY p.id DESC";
    
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $registros = array();
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['foto'])) {
                    $row['foto'] = base64_encode($row['foto']);
                }
                $registros[] = $row;
            }
            $response = array('registros' => $registros);
            echo json_encode($response);
        } else {
            echo "[]";
        }
    }
    //FIN


    //OBTENER LOS DETALLES DE UN PRODUCTO
    if ($comando == "detalleproducto") {
        $id_producto = $_GET["id_producto"];
        $id_usuario = $_GET["id_usuario"];
        
        $sql = "
        SELECT
            p.id AS producto_id,
            p.id_usuario,
            p.nombre AS nombre_producto,
            p.descripcion,
            p.categoria,
            p.cantidad,
            p.foto,
            u.id AS usuario_id,
            u.usuario,
            d.id AS dato_id,
            d.socialig,
            d.socialfb,
            d.estado
        FROM
            producto AS p
        LEFT JOIN
            usuario AS u
        ON
            u.id = '{$id_usuario}'
        LEFT JOIN
            dato AS d
        ON
            u.id_dato = d.id
        WHERE
            p.id = '{$id_producto}';";
    
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (!empty($row['foto'])) {
                $row['foto'] = base64_encode($row['foto']);
            }
            $response = array('registros' => [$row]);
            echo json_encode($response);
        } else {
            echo json_encode(array('registros' => [])); // Respuesta JSON vacía si el producto no se encuentra
        }
    }
    
    // MOSTRAR MIS PRODUCTOS PUBLICADOS
    if ($comando == "home2") {
        // Necesitamos el ID del usuario para excluir sus propios productos
        $id = $_GET["id"];
        $sql = "SELECT * FROM `producto` WHERE id_usuario = '{$id}'";
    
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $registros = array();
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['foto'])) {
                    $row['foto'] = base64_encode($row['foto']);
                }
                $registros[] = $row;
            }
            $response = array('registros' => $registros);
            echo json_encode($response);
        } else {
            echo "[]";
        }
    }

    // MOSTRAR SOLAMENTE UN PRODUCTO QUE PUBLIQUÉ PARA EDITARLO
    if ($comando == "OneProduct") {
        // Necesitamos el ID del usuario para excluir sus propios productos
        $id = $_GET["id"];
        $sql = "SELECT * FROM `producto` WHERE id = '{$id}'";
    
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $registros = array();
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['foto'])) {
                    $row['foto'] = base64_encode($row['foto']);
                }
                $registros[] = $row;
            }
            $response = array('registros' => $registros);
            echo json_encode($response);
        } else {
            echo "[]";
        }
        
    }
    
    $conn->close();

?>