<?php
/**
 * Created by PhpStorm.
 * User: Tadeu17
 * Date: 2018-12-01
 * Time: 18:20
 */


require_once("custom/php/common.php");

if (is_user_logged_in() and current_user_can("manage_custom_forms")) {
    $state = $_REQUEST[estado];


//********************************************************CRIAÇÂO TABELA****************************************************
    if ($state != "inserir_novo" AND $state != "editar_form" AND $state != "inserir" AND $state != "atualizar_form_custom") {
        echo '<h3>Gestão de formulários customizados</h3>';

        // CRIAÇÃO DA TABELA
        echo '<table border cols="3" style="table-layout: auto; word-wrap: inherit;font-size: 0.5rem;">
              <tr>
              <td><b>Formulário customizado</b></td>
              <td><b> Id </b></td>
		      <td><b> Nome do atributo </b></td> 
		      <td><b> Tipo de valor </b></td>
		      <td><b> Nome do campo no formulário </b></td>
		      <td><b> Tipo no campo do formulário </b></td>
		      <td><b> Tipo de unidade </b></td>
		      <td><b> Ordem do campo no formulário  </b></td>
		      <td><b> Tamanho do campo no formulário  </b></td>
		      <td><b> Obrigatório </b></td>
		      <td><b> Estado </b></td>
		      <td><b> Ação </b></td>
		      </tr>';

        $form_custom = execute_query("SELECT * FROM custom_form");

        while ($row = mysqli_fetch_assoc($form_custom)) {
            $attribute_form = execute_query("SELECT attribute.id, attribute.name, attribute.value_type, attribute.form_field_name, attribute.form_field_type, attribute.unit_type_id, attribute.form_field_order, attribute.form_field_size, attribute.mandatory, attribute.state
                                                    FROM attribute, custom_form_has_attribute, custom_form
                                                    WHERE attribute.id=custom_form_has_attribute.attribute_id AND custom_form.id = custom_form_has_attribute.custom_form_id AND custom_form.name = '{$row["name"]}' ");

            $num_attribute_form = mysqli_num_rows($attribute_form);

            $assegura_tabela = $num_attribute_form;

            if ($num_attribute_form == 0) {
                $assegura_tabela = 1;
            }

            echo '<tr> <td rowspan=' . $assegura_tabela . '><a href="?estado=editar_form&id=' . $row["id"] . '">' . $row["name"] . '</a></td>';

            if ($num_attribute_form == "0") {
                echo '<td colspan="11" align="center">  VAZIO  </td></tr>';

            } else {
                //row_ guarda os atributos da linha que está a ser analizada
                while ($row_ = mysqli_fetch_assoc($attribute_form)) {
                    echo '<td>' . $row_["id"] . '</td>';
                    echo '<td>' . $row_["name"] . '</td>';
                    echo '<td>' . $row_["value_type"] . '</td>';
                    echo '<td>' . $row_["form_field_name"] . '</td>';
                    echo '<td>' . $row_["form_field_type"] . '</td>';
                    echo '<td>' . $row_["unit_type_id"] . '</td>';
                    echo '<td>' . $row_["form_field_order"] . '</td>';
                    echo '<td>' . $row_["form_field_size"] . '</td>';
                    echo '<td>' . $row_["mandatory"] . '</td>';
                    echo '<td>' . $row_["state"] . '</td>';
                    echo '<td> <a href="">[editar]</a> <br> <a href="">[desativar]</a> </td>';
                    echo '</tr>';
                }
            }

        }
        echo '</table>';

        //Formulário que permite criação de um novo formulario customizado
        echo '<form method="post" action="./">
              <input type="hidden" value="inserir_novo" name="estado">
              <input type="submit" value="Inserir um novo formulário customizado" name="submit">
              </form>';
    } //********************************************************EDITAR UM FORM EXISTENTE****************************************************
    else if ($state == "editar_form") {
        $get_id = $_REQUEST["id"];
        echo $get_id;
        $custom_form_query_find_name = execute_query("SELECT name FROM custom_form WHERE id= '{$get_id}'");
        // O nome do formulário a editar e dado utilizando $name_custom_form["name"]
        $name_custom_form = mysqli_fetch_assoc($custom_form_query_find_name);
        $name_form=$name_custom_form["name"];
        echo '<h3>Gestão de formulários customizados</h3>';

        echo '<form method="post" action="./">';

        echo 'Nome do formulário customizado: ';
        echo'<input type="text" name="name_custom_form_" value="'.$name_form.'">';

        echo '<table border cols="3" style="table-layout: auto; word-wrap: inherit;font-size: 0.5rem;">
             <tr>
             <td><b> Id </b></td>
		     <td><b> Nome do atributo </b></td> 
		     <td><b> Tipo de valor </b></td>
		     <td><b> Nome do campo no formulário </b></td>
		     <td><b> Tipo no campo do formulário </b></td>
		     <td><b> Tipo de unidade </b></td>
		     <td><b> Ordem do campo no formulário  </b></td>
		     <td><b> Tamanho do campo no formulário  </b></td>
    	     <td><b> Obrigatório </b></td>
	         <td><b> Estado </b></td>
		     <td><b> Escolher </b></td>
		     <td><b> Ordem</b></td>
		     </tr>';

        $table_attribute = execute_query("SELECT * FROM attribute");

        while ($tuplo = mysqli_fetch_assoc($table_attribute)) {
            echo '<tr><td>' . $tuplo["id"] . '</td>';
            echo '<td>' . $tuplo["name"] . '</td>';
            echo '<td>' . $tuplo["value_type"] . '</td>';
            echo '<td>' . $tuplo["form_field_name"] . '</td>';
            echo '<td>' . $tuplo["form_field_type"] . '</td>';
            echo '<td>' . $tuplo["unit_type_id"] . '</td>';
            echo '<td>' . $tuplo["form_field_order"] . '</td>';
            echo '<td>' . $tuplo["form_field_size"] . '</td>';
            echo '<td>' . $tuplo["mandatory"] . '</td>';
            echo '<td>' . $tuplo["state"] . '</td>';


            //verificar se o atributo ja estava pre-selecionado!
            $tuplo_id = $tuplo["id"];

            $verify_attr_query = execute_query("SELECT * FROM custom_form_has_attribute WHERE custom_form_id = $get_id AND attribute_id = $tuplo_id");
            $nr_rows = mysqli_num_rows($verify_attr_query);
            if ($nr_rows > 0) {
                echo '<td><input type="checkbox" name="checkattr[]" value="' . $tuplo["id"] . '" CHECKED></td>';
            } else {
                echo '<td><input type="checkbox" name="checkattr[]" value="' . $tuplo["id"] . '" ></td>';
            }

            //verificar se o atributo ja tinha uma ordem
            $ordem_query = mysqli_fetch_assoc($verify_attr_query);
            echo '<td><input type="integer" name="ordem[]" size="3" value="' . $ordem_query["field_order"] . '"></td></tr>';
        }
        echo '</table>';

        echo '<input type="hidden" name="estado" value="atualizar_form_custom">';
        echo '<input type="hidden" name="id_form" value="' . $get_id . '">';
        echo '<input type="submit" value="Atualizar formulário customizado" name="submit">';
        echo '<input type="reset" value="Limpar" name="reset" >';
    } //********************************************************CRIAR UM NOVO FORM****************************************************
    else if ($state == "inserir_novo") {

        $custom_form_query_find_name = execute_query("SELECT name FROM custom_form WHERE id= '{$get_id}'");
        // O nome do formulário a editar e dado utilizando $name_custom_form["name"]
        $name_custom_form = mysqli_fetch_assoc($custom_form_query_find_name);

        echo '<h3>Gestão de formulários customizados</h3>';

        echo '<form method="post" action="./">';
        echo 'Nome do formulário customizado: <input type="text" name="name_custom_form" >';

        echo '<table border cols="3" style="table-layout: auto; word-wrap: inherit;font-size: 0.5rem;">
             <tr>
             <td><b> Id </b></td>
		     <td><b> Nome do atributo </b></td> 
		     <td><b> Tipo de valor </b></td>
		     <td><b> Nome do campo no formulário </b></td>
		     <td><b> Tipo no campo do formulário </b></td>
		     <td><b> Tipo de unidade </b></td>
		     <td><b> Ordem do campo no formulário  </b></td>
		     <td><b> Tamanho do campo no formulário  </b></td>
    	     <td><b> Obrigatório </b></td>
	         <td><b> Estado </b></td>
		     <td><b> Escolher </b></td>
		     <td><b> Ordem</b></td>
		     </tr>';

        $table_attribute = execute_query("SELECT * FROM attribute");

        while ($tuplo = mysqli_fetch_assoc($table_attribute)) {
            echo '<tr><td>' . $tuplo["id"] . '</td>';
            echo '<td>' . $tuplo["name"] . '</td>';
            echo '<td>' . $tuplo["value_type"] . '</td>';
            echo '<td>' . $tuplo["form_field_name"] . '</td>';
            echo '<td>' . $tuplo["form_field_type"] . '</td>';
            echo '<td>' . $tuplo["unit_type_id"] . '</td>';
            echo '<td>' . $tuplo["form_field_order"] . '</td>';
            echo '<td>' . $tuplo["form_field_size"] . '</td>';
            echo '<td>' . $tuplo["mandatory"] . '</td>';
            echo '<td>' . $tuplo["state"] . '</td>';
            echo '<td><input type="checkbox" name="checkattr[]" value="' . $tuplo["id"] . '" ></td>';
            echo '<td><input type="integer" name="ordem[]" size="3" ></td></tr>';
        }
        echo '</table>';

        echo '<input type="hidden" name="estado" value="inserir">';
        echo '<input type="submit" value="Inserir novo formulário customizado" name="submit">';
        echo '<input type="reset" value="Limpar" name="reset" >';
    } //********************************************************INSERIR NA BD UM NOVO FORM****************************************************
    else if ($state == "inserir") {
        $array_attr = $_REQUEST["checkattr"];
        //$array_attr[$i] devolve o id na posição $i
        $array_ordem = $_REQUEST["ordem"];
        //$array_ordem[$i] devolve o valor da ordem do formulario


        $name_form = $_REQUEST["name_custom_form"];

        $insert_name_id = returnIdInsert("INSERT INTO custom_form (custom_form.name) VALUE ('{$name_form}')");

        for ($i = 0; $i < sizeof($array_attr); $i++) {
            if ($array_attr[$i] != NULL AND $array_ordem[$i] != NULL) {
                execute_query("INSERT INTO custom_form_has_attribute (custom_form_id, attribute_id, field_order)
                                      VALUE ('{$insert_name_id}','{$array_attr[$i]}','{$array_ordem[$i]}')");
            } else if ($array_attr[$i] != NULL AND $array_ordem[$i] == NULL) {
                execute_query("INSERT INTO custom_form_has_attribute (custom_form_id, attribute_id)
                                      VALUE ('{$insert_name_id}','{$array_attr[$i]}')");
            }


        }

        echo 'Inserção feita com sucesso clique';
        blueButton("./", "Aqui");
        echo ' para ir para Gestão de Formulários.';

    } //********************************************************REALIZAR UPDATE DE UM FORM EXISTENTE****************************************************
    else if ($state == "atualizar_form_custom") {

        $rename_custom = $_REQUEST["name_custom_form_"];
        $array_attr = $_REQUEST["checkattr"];
        //$array_attr[$i] devolve o id na posição $i
        $array_ordem = $_REQUEST["ordem"];
        //$array_ordem[$i] devolve o valor da ordem do formulario

        $form_id = $_REQUEST["id_form"];
        $name_form = $_REQUEST["name_custom_form_"];

        //Fazer update ao nome do custom form
        execute_query("UPDATE custom_form SET name = '{$rename_custom}' WHERE id='{$form_id}'");

        execute_query("DELETE FROM custom_form_has_attribute WHERE custom_form_id = $form_id ");

        for ($i = 0; $i < sizeof($array_attr); $i++) {
            if ($array_attr[$i] != NULL AND $array_ordem[$i] != NULL) {
                execute_query("INSERT INTO custom_form_has_attribute (custom_form_id, attribute_id, field_order)
                                      VALUE ('{$form_id}','{$array_attr[$i]}','{$array_ordem[$i]}')");
            } else if ($array_attr[$i] != NULL AND $array_ordem[$i] == NULL) {
                execute_query("INSERT INTO custom_form_has_attribute (custom_form_id, attribute_id)
                                      VALUE ('{$form_id}','{$array_attr[$i]}')");
            }


        }
        echo 'Atualização feita com sucesso clique';
        blueButton("./", "Aqui");
        echo ' para ir para Gestão de Formulários.';

    }


} //********************************************************CASO NÃO TENHA ACESSO****************************************************
else {
    echo 'Não tem autorização para aceder a esta página';
}
?>