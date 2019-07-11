<?php
/**
 * Created by PhpStorm.
 * User: Tadeu17
 * Date: 2018-12-17
 * Time: 15:01
 */


require_once("custom/php/common.php");
require_once ('custom/php/spout/src/Spout/Autoloader/autoload.php');

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;


if (is_user_logged_in() and current_user_can("values_import")) {
    $state = $_REQUEST[estado];

    if ($state == NULL) {
        echo '<h3>Importação de Valores - escolher objeto</h3>';

        $query_tipos_objetos = execute_query("SELECT * FROM obj_type");
        echo '<ul><li>Objetos</li><ul>';
        while ($tipo_objetos = mysqli_fetch_assoc($query_tipos_objetos)) {
            echo '<li>' . $tipo_objetos["name"] . '</li><ul>';
            $tipo_objeto_id = $tipo_objetos["id"];
            $query_objeto = execute_query("SELECT * FROM object WHERE obj_type_id = '{$tipo_objeto_id}'");

            while ($objeto = mysqli_fetch_assoc($query_objeto)) {
                echo '<li><a href="?estado=introducao&obj=' . $objeto["id"] . '">[' . $objeto["name"] . ']</a></li>';
            }
            echo '</ul>';
        }
        echo '</ul>';
        echo '</ul>';
        echo '<ul><li>Formulários customizados</li><ul>';

        $query_form_customizados = execute_query("SELECT * FROM custom_form");

        while ($form_customizado = mysqli_fetch_assoc($query_form_customizados)) {
            echo '<li><a href="?estado=introducao&form=' . $form_customizado["id"] . '"> [' . $form_customizado["name"] . ']</a></li>';
        }
        echo '</ul>';
        echo '</ul>';
    } //CASO O ESTADO SEJA IGUAL A INTRODUÇÃO
    else if ($state == introducao) {
        //estado introdução
        $obj_id_req = $_REQUEST["obj"];
        $form_cust_id_req = $_REQUEST["form"];

        if ($_REQUEST["obj"]) {
            $atributos_objeto_query = execute_query("SELECT * FROM attribute WHERE obj_id=$obj_id_req");

            echo '<table border cols="3" style="table-layout: auto; word-wrap: inherit;font-size: 0.5rem;">';
            echo '<tr>';


            while ($atributos_objeto = mysqli_fetch_assoc($atributos_objeto_query)) {
                $id_attr = $atributos_objeto["id"];
                $value_query = execute_query("SELECT * FROM attr_allowed_value WHERE $id_attr=attribute_id ");
                $m = mysqli_num_rows($value_query);

                $i = 0;

                if ($m > 0) {
                    while ($i != mysqli_num_rows($value_query)) {
                        echo '<td><b>' . $atributos_objeto["form_field_name"] . '</b></td>';
                        $i = $i + 1;
                    }
                } else {
                    echo '<td><b>' . $atributos_objeto["form_field_name"] . '</b></td>';
                }
            }
            echo '</tr>';
            echo '<tr>';
            $atributos_objeto_query = execute_query("SELECT * FROM attribute WHERE obj_id=$obj_id_req");
            while ($atributo_objeto = mysqli_fetch_assoc($atributos_objeto_query)) {

                $id_attr = $atributo_objeto["id"];
                $atributos_objeto_value_type = $atributo_objeto["value_type"];
                $atributos_objeto_id = $atributo_objeto["id"];
                if ($atributos_objeto_value_type == 'enum') {

                    $value_query = execute_query("SELECT * FROM attr_allowed_value WHERE $id_attr=attribute_id ");
                    while ($value = mysqli_fetch_assoc($value_query)) {
                        echo '<td>' . $value["value"] . '</td>';
                    }

                } else {
                    echo '<td> Não é ENUM </td>';
                }

            }
            echo '</tr></table>';


        } else if ($_REQUEST["form"]) {
            // id do custom form : $form_cust_id_req;
            echo '<table border cols="3" style="table-layout: auto; word-wrap: inherit;font-size: 0.5rem;">';
            echo '<tr>';

            $attribute_id_query = execute_query("SELECT * FROM custom_form_has_attribute WHERE $form_cust_id_req=custom_form_id ");

            while ($atributos_form = mysqli_fetch_assoc($attribute_id_query)) {
                $id_attr = $atributos_form["attribute_id"];
                $value_query = execute_query("SELECT * FROM attr_allowed_value WHERE $id_attr=attribute_id ");
                $m = mysqli_num_rows($value_query);
                $i = 0;
                $atributos_query = execute_query("SELECT * FROM attribute WHERE $id_attr=id");
                $attr_tuplo = mysqli_fetch_assoc($atributos_query);

                if (m > 0) {
                    while ($i != mysqli_num_rows($value_query)) {
                        echo '<td>' . $attr_tuplo["form_field_name"] . '</td>';
                    }
                } else {
                    echo '<td>' . $attr_tuplo["form_field_name"] . '</td>';
                }
            }
            echo '</tr>';

            echo '<tr>';

            $attribute_id_query = execute_query("SELECT * FROM custom_form_has_attribute WHERE $form_cust_id_req=custom_form_id ");
            while ($atributo_form = mysqli_fetch_assoc($attribute_id_query)) {
                $id_attr = $atributo_form["attribute_id"];
                $dados_attr_query = execute_query("SELECT * FROM attribute WHERE $id_attr=id");
                $dados_attr = mysqli_fetch_assoc($dados_attr_query);
                $value = $dados_attr["value"];

                if ($value == 'enum') {
                    $value_query = execute_query("SELECT * FROM attr_allowed_value WHERE $id_attr=attribute_id ");
                    while ($value = mysqli_fetch_assoc($value_query)) {
                        echo '<td>' . $value["value"] . '</td>';
                    }
                } else {
                    echo '<td>Não é ENUM</td>';
                }

            }
            echo '</tr></table>';


        }

        echo '<br>     Deverá copiar a tabela acima e colar em um ficheiro excel, 
              e introduzir os valores a inserir nos respectivos lugares, no caso de ser enum marcar com um 0 caso o valor não se aplique à instancia, e inserir atribuir 1 quando se aplica.
              <br> O nome do ficheiro terá de estar em upload.xlsx
                <form method="post" action="" enctype="multipart/form-data">
                <input type="file" name="excelFile" id ="excelFile" accept=".csv, .xlsx,.xls,.ods, .xml">
                <input type="hidden" value="insercao" name="estado">
                <input type="submit" value="Upload ficheiro" name="submit">';
    }




    //CASO O ESTADO SEJA IGUAL A INSERÇÃO (NÃO ESTÁ A FUNCIONAR!!!!!!!!!)
    else if ($state == insercao)
    {

        $move=$_FILES["excelFile"]["name"];


        if (move_uploaded_file($_FILES["excelFile"]["tmp_name"],$move))
        {


            $reader = ReaderFactory::create(Type::XLSX); // for XLSX files


            $reader->open('upload.xlsx');

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {

                }
            }

            $reader->close();
        }
        else {
            echo 'Erro no upload do ficheiro!';
        }

    }
    else {
        echo 'Não tem autorização para aceder a esta página';
    }
}
?>