<?php
require_once("custom/php/common.php");
echo '<script src="/custom/js/validation_gestao-de-unidade.js"></script>';

if(is_user_logged_in() and current_user_can("manage_unit_types")) {
    $est = $_REQUEST["estado"]; //Guarda o valor do estado na variavel est


    if (!$est) {   //Verificamos se o estado $est é NULLl
        echo' <table>
         <tr>
			      
			      <th> ID </th> <th> Unidade </th> 
			      
			     </tr>';

        preencheTabela();

       echo '
        
        <h3> <b> <i>  Gestão de unidades - introdução </i></b>  </h3>
        
        
        <!-- caixas de texto, botão -->
			<form id="form1" onsubmit="return validateForm()"  method="post">  
			Nome: <input type="text" name="text_name" autocomplete=\'off\'></br>
			<input type="hidden" name="estado" value="inserir"> </br></br>
			<input type="submit" value="Submit" name="nome">
        
        ';

    } else{ //Modo inserir estado = "inserir"
        if(!strcmp($est,"inserir")){  // verificamos se existe alguma mudança de estado  ( ex:Botão a ser usado)

            echo '<h3><b><i> Gestão de Unidades - Inserção  </h3></b></i>';

            $valorNome = $_REQUEST['text_name']; //Vamos buscar o texto que o utilizador inseriu

            if($valorNome) { // caso a caixa de texto nao esteja vazia

                inserirDados($valorNome); // vai buscar a função inseredados e insere o que esta no $valorNome na BD

               echo ' <br><br> <i>Clique em ';
            blueButton("gestao-de-unidades", "Continuar");//botao para voltar a pagina gesta-de-unidades
                echo 'para avançar</i>';

            }else{

                printf("O campo da unidade não pode estar vazio.");

                echo '<br><br> <i>Clique em ';
                blueButton("gestao-de-unidades", "Continuar");//botao para voltar a pagina gesta-de-unidades
                echo 'para avançar</i>';
            }
        }

    }


} else {
    printf("Não tem autorização para aceder a esta página");
}


function preencheTabela()
{

    $sql = "SELECT * FROM  attr_unit_type ORDER BY name";
    $resultado = execute_query($sql);

    if (mysqli_num_rows($resultado) > 0) // Vê se o resultado da query tem pleo n
    {


        while ($row = mysqli_fetch_assoc($resultado))  //Enquanto tiver linhas vai preenchendo a tabela
        {

            echo '
				 <tr>
				  
				   <td> '.$row["id"].'  </td> 
				   <td> '.$row["name"].'</td> 
				  
				   
				  </tr>';

        }
        echo  '	</table>';
    }


    else

    {
        printf("Não há tipos de unidades.");
    }
}

function inserirDados($unidade){

    $inserir = "INSERT INTO attr_unit_type(name) VALUES ('$unidade')";   //query para inserir uma nova unidade
    $result = execute_query($inserir);

    if(!$result){ //caso tenha ocorrido um erro

        printf("Ocorreu um erro a inserir a unidade.");

    } else { // caso nao tenha ocorrido um erro

        printf(" Inseriu uma unidade com sucesso.");


    }


}

?>