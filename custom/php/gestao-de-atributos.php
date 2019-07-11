<?php
/**
 * Created by PhpStorm.
 * User: Tadeu17
 * Date: 2018-12-01
 * Time: 18:20
 */

require_once("custom/php/common.php");

echo '<script src="/custom/js/JavaScriptValidation_LA.js"></script>';

/*
	verifica se o loggin foi efectuado e caso esteja com login efectuado este verifica se tem a capability Manage unit types
*/
if (is_user_logged_in() and current_user_can("manage_attributes")) {
    /**/
    $exeState = $_REQUEST["estado"];


    /* caso não haja estado de execução especifico */
    if ($exeState != "inserir") {
        fillTable();
        criaFormulario();
    } else {

        $atribute_name = $_REQUEST["nome"];
        $value_type = $_REQUEST["tipo_valor"];
        $object_type = $_REQUEST["tipo_objecto"];
        $field_type = $_REQUEST["tipo_campo"];
        $unit_type = $_REQUEST["tipo_unidade"];
        $field_order = $_REQUEST["ordem_campo"];
        $field_size = $_REQUEST["tamanho_campo"];
        $mandatory = $_REQUEST["obrigatorio"];
        $ref_object = $_REQUEST["objecto_ref"];

        if(DO_SERVER_SIDE_VALIDATION)
        {
        if (!empty($atribute_name) and !empty($value_type) and !empty($object_type) and ($field_type != null) and  ($field_order != NULL) and !empty($mandatory) and !empty($ref_object)) {

            //VAI VERIFICAR SE SE O FIELD TYPE É TEXT OU TEXT BOX
            if (($field_type == "text" AND empty($field_size)) or ($field_type == "textbox" AND empty($field_size))) {
                //INSERIR NA BASE DE DADOS
                insertERROR("1");

            } else
                {
                if ($field_order > "0" AND is_numeric($field_order)) {
                        insertDB();
                }
                else
                 {
                    insertERROR("3");
                 }

            }
        } else {
            //ERRO FALTA INSERIR ALGO
            insertERROR("2");
        } }
        else
        {
            insertDB();
        }
    }


} else {
    printf("Não tem autorização para aceder a esta página");
}


// Função responsavel por preencher a tabela
function fillTable()
{
    // objetos guarda uma tabela com todos os objetos
    $objectos = execute_query("SELECT * FROM object ");
    //$nr_objetos guarda o numero de tuplos em objetos
    $nr_objectos = mysqli_num_rows($objectos);
    if ($nr_objectos == 0) {
        printf("Não existem propriedades especificadas");
    } else {
        echo '<table border cols="3" style="table-layout: auto; word-wrap: inherit;font-size: 0.5rem;">

		<tr> 
		<td> <b> Objeto </b> </td> 
		<td> <b> id </b> </td>
		<td> <b> Nome do atributo </b> </td> 
		<td> <b> Tipo de valor </b> </td>
		<td> <b> Nome do campo no formulário </b> </td>
		<td> <b> Tipo no campo do formulário </b> </td>
		<td> <b> Tipo de unidade </b> </td>
		<td> <b> Ordem do campo no formulário  </b> </td>
		<td> <b> Tamanho do campo no formulário  </b> </td>
		<td> <b> Obrigatório </b> </td>
		<td> <b> Estado </b> </td>
		<td> <b> Ação </b> </td>
		</tr> ';


        //row guarda o linha que está a ser analizada
        while ($row = mysqli_fetch_assoc($objectos)) {

            // Tabela com os atributos necessários para preencher a tabela
            $tipo_objectos = execute_query("
				SELECT attribute.id, attribute.name, attribute.value_type, attribute.form_field_name, attribute.form_field_type, attribute.unit_type_id, attribute.form_field_order, attribute.form_field_size, attribute.mandatory, attribute.state
				FROM attribute, object 
				WHERE attribute.obj_id = object.id AND object.name ='{$row["name"] }'");
            //faz a ligação da tabela objetos com a tabela atributo, e o nome é igual ao objeto a ser analizado

            $nr_tipo_objectos = mysqli_num_rows($tipo_objectos);

            $assegura_tabela = $nr_tipo_objectos;
            if ($nr_tipo_objectos == "0") {
                $assegura_tabela = 1;
            }


            echo '<tr> <td rowspan=' . $assegura_tabela . '>' . $row["name"] . '</td>';

            // Caso o objeto nao tenha atributo.
            if ($nr_tipo_objectos == "0") {
                echo '<td colspan="11" align="center">  VAZIO  </td></tr>';

            } else {
                //row_ guarda os atributos da linha que está a ser analizada
                while ($row_ = mysqli_fetch_assoc($tipo_objectos)) {
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
                    echo '<td> <a href="">[editar] </a><br><a href=""> [desativar]</a> </td>';
                    echo '</tr>';
                }
            }

        }
        echo '</table>';

        //}
    }


//Função que cria Formulario
    function criaFormulario()
    {
//*********************************************************Subtitulo****************************************************

        echo '<h3> <b><i>Gestão de atributos - introdução</i><b><br></h3>';

        //input NOME

        echo '
	<form method="post" action="./" name="Formulario_Gestao_Atributos" onsubmit="return makeServerSideValidation(\''.DO_SERVER_SIDE_VALIDATION.'\')"> 
	Nome do atributo:<br><input type="text" name="nome"/> <br>';

//******************************************** RADIO: TIPO DE VALOR ****************************************************

        echo '<br><b>Tipo de valor:</b><br>';

        // guarda em array_enum_types um array que devolve os types.
        $array_enum_types = getEnumValues("attribute", "value_type");
        $tamanho_value_types = sizeof($array_enum_types);


        for ($i = 1; $i < $tamanho_value_types; $i++) {
            echo '<input type="radio" name="tipo_valor" autocomplete="off" value="' . $array_enum_types[$i] . '">' . $array_enum_types[$i] . '<br>';
        }
//****************************************** SELECT BOX:OBJECTO A QUE IRÁ PERTENCER O ATRIBUTO***********************************************
        echo '<br><b>Tipo de Objecto:</b><br>';

        $nome_objectos = execute_query("SELECT * FROM object");


        echo '<select name="tipo_objecto">';
        $i = 1;


        while ($row = mysqli_fetch_assoc($nome_objectos)) {
            echo '<option value=' . $row["id"] . ' > ' . $row["name"] . ' </option>';
            $i++;
        }
        echo '</select> <br>';

//******************************************** RADIO: TIPO DO CAMPO DO FORMULARIO **************************************

        echo '<br><b>Tipo do campo do formulário:</b><br>';

        $array_enum_field = getEnumValues("attribute", "form_field_type");
        $tamanho_value_field = sizeof($array_enum_types);


        for ($i = 1; $i < $tamanho_value_field; $i++) {
            echo '<input type="radio" name="tipo_campo" autocomplete="off"  value="' . $array_enum_field[$i] . '">' . $array_enum_field[$i] . '<br>';
        }

//****************************************** SELECT BOX: TIPO DE UNIDADE ***********************************************

        echo '<br><b>Tipo de Unidade:</b><br>';

        $nome_unidade = execute_query("SELECT * FROM attr_unit_type");


        echo '<select name="tipo_unidade">';
        echo '<option value=0>';
        $i = 1;


        while ($row = mysqli_fetch_assoc($nome_unidade)) {
            echo '<option value=' . $row["id"] . '> ' . $row["name"] . ' </option>';
            $i++;
        }
        echo '</select> <br>';

//****************************************** Insere : int ordem campo **************************************************

        echo 'Ordem no campo no formulário:<br><input type="integer" name="ordem_campo"/> <br>';

//****************************************** Insere : Tamanho do campo no formulário ***********************************


        echo 'Tamanho do campo no formulário:<br> <input type="integer" name="tamanho_campo"/> <br>';


        //****************************************** Radio: Obrigatorio ****************************************************

        echo '<br><b>Obrigatório:</b><br>';


        echo '<input type="radio" name="obrigatorio" autocomplete="off" value="1" > Sim <br>';
        echo '<input type="radio" name="obrigatorio" autocomplete="off"  value="0">Não<br>';

//****************************************** SELECT BOX : Objecto referenciado por este atributo ***********************

        echo '<br><b>Objecto referênciado por este atributo</b><br>';

        $nome_objectos = execute_query("SELECT * FROM object");


        echo '<select name="objecto_ref">';
        $i = 1;


        while ($row = mysqli_fetch_assoc($nome_objectos)) {
            echo '<option value=' . $row["id"] . '> ' . $row["name"] . ' </option>';
            $i++;
        }
        echo '</select> <br><br>';

//****************************************** HIDEN *********************************************************************
        echo '<input type="hidden" value="inserir" name="estado">';
//****************************************** SUBMIT ********************************************************************
        echo '<input type="submit" value="Inserir Atributo" name="submit">';
        //****************************************** RESET ********************************************************************
        echo '<input type="reset" value="Limpar" name="reset">';
        echo '</form>';
    }
}

function insertERROR($i)
{
    if ($i == 1) {
        echo 'Visto que escolheu na opção <b>tipo do campo de profundida </b> : text ou textbox tem obrigatoriamente que prencher o
campo <b>Tamanho do campo formulário</b> para voltar a tras clique ';
        blueButton("./", "AQUI.");

    }

    if ($i == 2) {
        echo 'Dados em falta clique ';
        blueButton("./", "AQUI");
        echo ' para voltar atrás e preencher os mesmos.';
    }

    if ($i == 3) {
        echo 'Tenha em atenção que o valor de em <b> ordem do formulário em campo</b> tem de ser superior a 0, clique  ';
        blueButton("./", "AQUI");
        echo ' para voltar atrás e preencher corretamente.';
    }
}

function insertDB()
{
    $atribute_name = $_REQUEST["nome"];
    $object_type = $_REQUEST["tipo_objecto"];
    $ref_object = $_REQUEST["objecto_ref"];
    $value_type = $_REQUEST["tipo_valor"];
    $field_type = $_REQUEST["tipo_campo"];
    $unit_type = $_REQUEST["tipo_unidade"];
    $field_size = $_REQUEST["tamanho_campo"];
    $field_order = $_REQUEST["ordem_campo"];
    $mandatory = $_REQUEST["obrigatorio"];



    if((substr($field_size,2,1)=="x" AND substr($field_size,0,2)>"0" AND substr($field_size,3,2)> "0") OR empty($field_size))
    {
        //INSERÇÃO DOS DADOS DO FORMULÁRIO NA BD

        $insert="INSERT INTO attribute (name, obj_id, rel_id, value_type, form_field_name, form_field_type, unit_type_id, form_field_size, form_field_order, mandatory, state, obj_fk_id)
                 VALUES ('{$atribute_name}', $object_type, NULL , '{$value_type}','TEMPORARIO', '{$field_type}', $unit_type, '{$field_size}', $field_order, $mandatory, 'active', $ref_object )" ;
        $id_insert = returnIdInsert($insert);

        $obj_name_query = execute_query("SELECT name FROM object WHERE id=$object_type");
        $obj_name=mysqli_fetch_assoc($obj_name_query);

        $field_name= ''.substr($obj_name["name"],0,3).'_'.$id_insert.'_'.$atribute_name;

        $update ="UPDATE attribute SET form_field_name = '{$field_name}'  WHERE id=$id_insert";

        execute_query($update);

        echo 'Inserido com sucesso, clique ';
        blueButton("./","AQUI");
        echo ' para continuar';

    }
    else
    {
        echo 'O tamanho do campo do formulário não esta no formato aaxbb, em que aa representa o numero de colunas e bb o numero de linhas, Clique ';
        blueButton("./","AQUI");
        echo ' para preencher novamente.';
    }



}
?>

