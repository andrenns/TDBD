<?php
require_once("custom/php/common.php");

echo '<script src="/custom/js/validations.js"></script>';

if(is_user_logged_in()  and current_user_can("manage_objects"))
{
    //obtem estado de execução
    $exec_state = $_REQUEST["estado"];

    //se nao houver um estado de execução especifico
    if(!$exec_state)
    {
        //retorna os tuplos da tabela object
        $object_table = execute_query("SELECT * FROM object ");

        //numero de tuplos da tabela object
        $object_num_rowns = mysqli_num_rows($object_table);

        //se nao houver tuplos na tabela object retorna uma mensagem
        if(!$object_num_rowns)
        {
            printf("Não há objetos <br>");
        }
        else
        {
            //TABELA
            $wip = "wip";
            //retorna os tipos de objetos
            $object_type = getObjInfo(1);

            //retorna o numero de tipos de objetos
            $num_objet_type = getObjInfo(2);

            //primeira linha da tabela
            echo '<table style="text-align: left; width:100%" cellspacing="2" cellpadding = "2" border = "1">
            <tr>
            <th rowspan = "1"> <b>tipo de objeto</b> </th>
            <th> <b>id</b> </th>
            <th> <b>nome do objeto</b> </th>
            <th> <b>estado</b> </th>
            <th> <b>ação</b> </th>
          </tr>';

            //para cada tipo de objeto escreve os nomes e ids dos objetos associados a esse tipo
            for($i = 0; $i < $num_objet_type; $i++)
            {
                //retorna a linha  do tipo 'atual'
                $row_type = mysqli_fetch_assoc($object_type);

                //retorna os objetos do tipo 'atual'
                $objects = execute_query("SELECT id, object.name, state FROM object WHERE object.obj_type_id = {$row_type["id"]} ORDER BY object.name ASC");

                //retorna o numero de objetos desse tipo
                $nr_objects = mysqli_num_rows($objects);

                if($nr_objects!=0)
                {
                    echo '<tr>
              <td rowspan=' . $nr_objects . '> ' . $row_type["name"] . '</td>';
                }
                //para cada objeto escreve id, nome e estado
                for($j=0; $j <$nr_objects; $j++)
                {
                    //retorna a linha  do objecto 'atual'
                    $row_object = mysqli_fetch_assoc($objects);

                    if($row_object["state"]=="active")
                    {
                        $row_object["state"]="ativo";
                    }
                    if($row_object["state"]=="inactive")
                    {
                        $row_object["state"]="inativo";
                    }

                    //desenha a linha para cada objeto
                    echo '<td> ' .$row_object["id"]. '</td>
                            <td>' .$row_object["name"]. '</td>
                            <td>' .$row_object["state"]. '</td>
                            <td> <a href="javascript:alert(\''.$wip.'\')">[editar]</a> <a href="javascript:alert(\''.$wip.'\')">[desativar] </a></td>
                            </tr>
                            ';
                }
            }
            echo '</table>';
        }
        //FORMULARIO
        //subtitulo

        echo '<fieldset><legend><h3> <b> <i> Gestão de objetos - introdução </i> </b></h3></legend>';



        echo '<form method="post" name="gestao-de-objetos_introducao" onsubmit="return performCSValidationGO(\''.DO_SERVER_SIDE_VALIDATION.'\')">
            Nome: <br>
            <input type="text" name="obj_name" autocomplete="off"><br>
            
            <br> Tipo: <br>';

        //retorna os tipos de objetos
        $obj_type = getObjInfo(1);

        //retorna o nr de tipos de objetos
        $nr_obj_type = getObjInfo(2);

        //para cada tipo de objeto cria um input do tipo radio
        for($i = 0; $i < $nr_obj_type; $i++)
        {
            //retorna a linha do tipo 'atual'
            $obj_type_row = mysqli_fetch_assoc($obj_type);

            echo '<input type="radio" name="obj_type" autocomplete="off" value='.$obj_type_row["id"].'>'.$obj_type_row["name"].'<br>
        ';
        }
        echo '<br> Estado: <br>';

        //array com os estados possiveis
        $states_possible = getEnumValues("object", "state");

        //nr de estados possiveis
        $nr_states_possible = count($states_possible);

        //para cada estado possivel cria um input do tipo radio
        for($i = 1; $i <= $nr_states_possible; $i++)
        {

            if($states_possible[$i]=="active")
            {
                $state="ativo";
            }
            if($states_possible[$i]=="inactive")
            {
                $state="inativo";
            }
            echo '<input type="radio" name="obj_state" autocomplete="off" value='.$states_possible[$i].'>'
                .$state.'<br>';
        }

        echo '<input type="hidden" value="inserir" name="estado" ><br>
            <input type="submit" value="Inserir Objeto" name="submit" ><br>
</fieldset>
            </form>';
    }
    else //caso contrario se houver um estado de execuçao especifico
    {
        //caso o estado seja "inserir"
        if($exec_state=="inserir")
        {
            echo '<h3> <b> <i> Gestão de objetos - inserção </i> </b></h3><br>';
		
		//flag para apenas mostrar mensagem de erro se algum campo ficar por preencher
		$flag = false;
            //se a constante for definida a true
            if(DO_SERVER_SIDE_VALIDATION)
            {
               		//verifica se algum campo está vazio e se estiver apresenta uma mensagem de erro com uma opção para voltar para tras
		    if(empty($_REQUEST["obj_name"]))
		    {
			echo '<i> O campo do nome ficou por preencher </i><br>';
			$flag = true;
				
		    }
			if(empty($_REQUEST["obj_type"]))
			{
				echo '<i> Necessita selecionar um tipo de objeto </i><br>';
				$flag = true;
			}

			if(empty($_REQUEST["obj_state"]))
			{
				echo '<i> Nessecita selecionar um estado </i><br>';
				$flag = true;
			}
		if($flag)
		{
		echo '<i>Certifique-se que preenche os campos todos!</i><br> <i>Clique </i>';
			blueButton("gestao-de-objetos", "aqui");
			echo '<i> para voltar à página anterior</i>';
			return 0;
		}
            }
             //variaveis a inserir
            $name =$_REQUEST["obj_name"];
            $obj_type_id = $_REQUEST["obj_type"];
            $state = $_REQUEST["obj_state"];

            //insere os objetos na tabela object
            execute_query("INSERT INTO object (name, obj_type_id, state) VALUES ('{$name}', '{$obj_type_id}', '{$state}') ");

            //mostra uma tabela com os dados que foram inseridos
            echo '<i><b>Inseriu os seguintes dados na tabela objetos com <u>sucesso</u>:  </b> </i> ';
            echo '<table border cols="2" style="table-layout: auto; word-wrap: inherit;">
              <tr><th>Nome:</th><td>' . $name . '</td></tr>
               <tr><th>Id do tipo de objeto:</th><td>' . $obj_type_id . '</td></tr>
                <tr><th>Estado:</th><td>' . $state . '</td></tr>
                </table>';

            echo '<i>Inseriu os dados de novo objeto com sucesso.</i><br>
                            <i>Clique em ';
            blueButton("gestao-de-objetos", "Continuar");//cria o botao para voltar a pagina gesta-de-objetos
            echo 'para avançar</i>';
        }
    }

}
else //user nao tem a capability manage_objects aparece mensagem
{
    printf("Não tem autorização para aceder a esta página");
}

/****************************************************************************/
/*Funçoes de ajuda*/
/***************************************************************************/
//retorna os tipos de objetos ou o numero de tipos de objetos
function getObjInfo($i)
{
    //retorna os tipos de objetos
    $object_type = execute_query("SELECT id,obj_type.name FROM obj_type ORDER BY obj_type.name ASC ");

    //retorna o numero de tipos de objetos
    $num_objet_type = mysqli_num_rows($object_type);

    if($i==1)
    {
        return $object_type;
    }
    elseif ($i==2)
    {
        return $num_objet_type;
    }
    else
    {
        print("Opção inválida!");
    }
}
?>
