<?php
require_once("custom/php/common.php");

echo '<script src="/custom/js/validations.js"></script>';

//se o utilizador tiver efetuado log in e tiver a capability manage allowed values
if(is_user_logged_in() and current_user_can("manage_allowed_values"))
{
    //obtem o estado de execução
    $exec_state = $_REQUEST["estado"];

    //se nao retornar nenhum estado na variavel request
    if(!$exec_state)
    {
        //retorna os atributos do tipo enum
        $attributes_enum = execute_query("SELECT * FROM attribute WHERE value_type = 'enum'");

        //nr de atributos do tipo enum
        $nr_attributes_enum = mysqli_num_rows($attributes_enum);

        //se não houverem atributos do tipos enum
        if(!$nr_attributes_enum)
        {
            printf("<i> Não há atributos especificados cujo tipo de valor seja enum. Especificar primeiro novo(s) atributo(s) e depois voltar a esta opção.</i>");
        }
        else //caso hajam atributos mostra a tabela
        {
            $wip = "wip";

            //primeira linha da tabela
            echo '<table style="text-align: left; width:100%" cellspacing="2" cellpadding = "2" border = "1">
            <tr>
            <th rowspan = "1"> <b>objeto</b> </th>
            <th> id </th>
            <th> <b>atributo</b> </th>
            <th> id </th>
            <th> valores permitidos </th>
            <th> estado </th>
            <th> ação </th>
          </tr>';

            //retorna todos os objetos do tipo enum
            $objects = execute_query("SELECT DISTINCT object.name, object.id FROM object,attribute WHERE object.id=attribute.obj_id and attribute.value_type='enum' ORDER BY object.name ASC ");

            //numero de objetos
            $nr_objects = mysqli_num_rows($objects);


            //para cada objeto escreve id do atributo, id do valor permitido, nome do valor permitido, estado e ação
            for($i = 0; $i < $nr_objects; $i++)
            {

                //linha do objecto 'atual'
                $row_object =  mysqli_fetch_assoc($objects);

                //retorna os atributos do objeto 'atual'
                $attributes = execute_query("SELECT DISTINCT attribute.name, attribute.id 
                                        FROM attribute,object 
                                        WHERE attribute.obj_id = {$row_object["id"]} 
                                        AND attribute.value_type ='enum' 
                                        ORDER BY attribute.name ASC");

                //retorna os valores permitidos para os atributos do objeto 'atual' mesmo que seja null 
                $allowed_values = execute_query("SELECT attr_allowed_value.value
FROM attribute left outer join attr_allowed_value on
attr_allowed_value.attribute_id = attribute.id
WHERE attribute.value_type='enum'
AND attribute.obj_id={$row_object["id"]}");

		
		

                //nr de valores permitidos
                $nr_allowed_values = mysqli_num_rows($allowed_values);

                //retorna o numero de atributos de um certo objeto
                $nr_attributes = mysqli_num_rows($attributes);

		echo '<tr>';
            
                echo ' <td rowspan=' . $nr_allowed_values . '>' . $row_object["name"] . '</td>';
		

                //para cada atributo escreve id, id do valor permitido, valor permitidio, estado e ação
                for($j = 0; $j < $nr_attributes; $j++)
                {
                    //linha do atributo 'atual'
                    $row_attribute = mysqli_fetch_assoc($attributes);

                    //valores permitidos do atributo 'atual'
                    $allowed_values_attr = execute_query("SELECT attr_allowed_value.id,attr_allowed_value.value, attr_allowed_value.state
                                                        FROM attr_allowed_value
							WHERE attr_allowed_value.attribute_id = {$row_attribute["id"]}
							ORDER BY attr_allowed_value.value ASC ");

                    //nr valores permitidos do atributo 'atual'
                    $nr_allowed_values_attr = mysqli_num_rows($allowed_values_attr);

			$href = "gestao-de-valores-permitidos?estado=introducao&atributo={$row_attribute["id"]}";
                        $message = "[{$row_attribute["name"]}]";

                    //se nao houver valores permitidos aparece a mensagem
                    if($nr_allowed_values_attr==0)
                    {
			echo'<td> ' . $row_attribute["id"] . ' </td>
			<td> <a href='.$href.'  style="text-decoration: none" > 
                <b> '.$message.' </b></a>
                 </td>  
                    <td colspan=4> <i> Não há valores permitidos </i></td></tr>';
                    }
			else //se houver apresenta como é pedido
			{

                        echo '<td rowspan=' . $nr_allowed_values_attr . ' > ' . $row_attribute["id"] . ' </td> 
                <td rowspan=' . $nr_allowed_values_attr . ' > <a href='.$href.'  style="text-decoration: none" > 
                <b> '.$message.' </b></a>
                 </td> ';
			
			
                    //para cada atributo escreve id do valor permitido, valor, ação e estado
                    for($k = 0; $k < $nr_allowed_values_attr; $k++)
                    {

                        //linha do valor permitido 'atual'
                        $row_allowed_value = mysqli_fetch_assoc($allowed_values_attr);

                        if( $row_allowed_value["state"]=="active")
                        {
                            $row_allowed_value["state"]="ativo";
                        }
                        if( $row_allowed_value["state"]=="inactive")
                        {
                            $row_allowed_value["state"]="inativo";
                        }

                        echo '<td> ' .$row_allowed_value["id"]. '</td>
                            <td>' .$row_allowed_value["value"]. '</td>
                            <td>' .$row_allowed_value["state"]. '</td>
                            <td> <a href="javascript:alert(\''.$wip.'\')">[editar]</a> <a href="javascript:alert(\''.$wip.'\')">[desativar] </a></td>
                            </tr>
                            ';
                    }
		}
                }

            }
            echo '</table>';	
        }

    }
    else //caso contrario se houver um estado de execução especifico
    {
        switch($exec_state)
        {
            case "introducao": //se o estado for introducao

                //valor da variavel atributo
                $_SESSION['attribute_id'] = $_REQUEST["atributo"];

                //subtitulo
                echo '<fieldset><legend><h3><b> Gestão de valores permitidos - introdução </b></h3></legend>';

                echo '<form method="post" name="gestao-de-valores-permitidos_introducao" onsubmit="return performCSValidationGVP(\''.DO_SERVER_SIDE_VALIDATION.'\')">
                Valor: <br>
                <input type="text" name="allowed_value" autocomplete="off"> <br>
                <input type="hidden" name="estado" value="inserir">
                <input type="submit" value="Inserir valor permitido" name="submit">
                </form></fieldset>';


                blueButton("gestao-de-valores-permitidos","Voltar à página anterior");

                break;

            case "inserir": //se o estado for inserir
                echo '<h3><b>Gestão de valores permitidos - inserção</b></h3>';

                $attribute_id = $_SESSION['attribute_id'];
                $value = $_REQUEST["allowed_value"];
                $state = "active";

                //verifica a constante que determina se a validação é feita em server side ou client side
                 if(DO_SERVER_SIDE_VALIDATION)
                 {
                     //verifica se o $value esta vazio e apresenta uma mensagem de erro se tiver
                     if (empty($value)) {
                         echo '<i> O nome do valor ficou por preencher </i><br>
				<i>Certifique-se que preenche antes pressionar o botão!</i><br> <i>Clique </i>';
                         backButton("aqui");
                         echo '<i> para voltar à página anterior</i>';
                         return 0;
                     }
                 }
                //insere um novo valor permitido
                execute_query("INSERT INTO attr_allowed_value (attribute_id, value, state) VALUES ('{$attribute_id}','{$value}', '{$state}') ");

                //renomeia valor para aparecer em portugues na tabela
                $state= "ativo";

                echo '<i><b>Inseriu os seguintes dados na tabela objetos com <u>sucesso</u>:  </b> </i> ';
                echo '<table border cols="2" style="table-layout: auto; word-wrap: inherit;">
                 <tr><th>id do atributo</th><td>' . $attribute_id . '</td></tr>
                 <tr><th>valor permitido</th><td>' . $value . '</td></tr>
                 <tr><th>estado</th><td>' . $state . '</td></tr>
                 </table>';

                echo '<i>Inseriu os dados de novo objeto com sucesso.</i><br>
                            <i>Clique em ';
                blueButton("gestao-de-valores-permitidos", "Continuar");//cria o botao para voltar a pagina gesta-de-objetos
                echo 'para avançar</i>';
                break;
        }
       
    }
}
else //se nao tiver login efetuado ou nao tiver a capability manage allowed values
{
    printf("Não tem autorização para aceder a esta página");
}

?>
