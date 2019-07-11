<?php
require_once("custom/php/common.php");


if(user_logged_in and current_user_can("insert_values")){
    $est = $_REQUEST["estado"]; //Guarda o valor do estado na variavel est

        Switch ($est){

            case "": // se o estado for null

                echo '<h3>   <b> <br>  Inserção de valores - escolher objeto/formulário customizado </br> </b> </h3>';

                listaDeObjetos(); // lista todos os atributos do objetos
                listaDeFormularios(); //lista todos os atributos do fomulario

             break;

            case "introducao": // se a varivael $est for igual a introdução

             if($_REQUEST["obj"]) { //Se for um objeto


                 $_SESSION["obj_id"] = $_REQUEST["obj"]; // guarda numa variavel de sessão o obj_id

                $obj = execute_query("Select object.name,object.obj_type_id from object Where id = '{$_SESSION["obj_id"]}'");
                $obj_LINHA = mysqli_fetch_assoc($obj);

                 $_SESSION["obj_name"] = $obj_LINHA["name"]; //guarda numa variavel de sessao o nome do objeto
                 $_SESSION["obj_type_id"] = $obj_LINHA["obj_type_id"];


                 echo ' <h3>   Inserção de valores  - '.$_SESSION["obj_name"] .' </h3>';

                 listaAtributosDoObj(); // lista todos os atributos de um certo objeto (inputs)

                 echo '.<br>.';


                 backButton("Voltar atrás");

             }else{
                 if($_REQUEST["form"])   {   //Se for um formulario


                     $_SESSION["form_id"] = $_REQUEST["form"]; // guarda numa variavel de sessao o id do formulario

                       //query que vai buscar o id o nome do custom form
                     $form = execute_query("Select *
                                      FROM custom_form
                                          where custom_form.id = '{$_SESSION["form_id"]}'");

                     $form_LINHA = mysqli_fetch_assoc($form);

                     $_SESSION["form_name"] = $form_LINHA["name"];

                  echo ' <h3>   Inserção de valores - '.$_SESSION["form_name"].' </h3>';


                  listaAtributosDoForm(); //agora tenho que listar os atributos que estão ligados ao formulario

                  echo '.<br>.';

                  backButton("Voltar atrás");
                 }

             }

                break;

            case "validar":  // se a variavel $est for igual a validar

             if($_REQUEST["obj"]) {


                 $_SESSION["obj_id"] = $_REQUEST["obj"]; // Guarda a informação acerca do objeto em variáveis de sessão

                 $obj = execute_query("Select object.name,object.obj_type_id from object Where id = '{$_SESSION["obj_id"]}'");
                 $obj_LINHA = mysqli_fetch_assoc($obj); //vai buscar um objeto

                 $_SESSION["obj_name"] = $obj_LINHA["name"];  //guarda um nome de um objeto numa variavel de sesao
                 $_SESSION["obj_type_id"] = $obj_LINHA["obj_type_id"]; //guarda o type_id numa variavel de sessao

                 echo '<h3>   <b> <br> Inserção de valores - ' . $_SESSION["obj_name"] . ' - validar  </br> </b> </h3>';

                 $atributosMandatory = ("SELECT attribute.id, attribute.name
                                          FROM attribute,object
                                          WHERE attribute.obj_id = object.id and attribute.obj_id = '{$_SESSION["obj_id"]}' and attribute.mandatory = '1' AND attribute.state='active'");

                 $res_atributosMandatory = execute_query($atributosMandatory); //query que vai buscar os atributos de um objeto que sao obrigatorios

                 campoObrigatorio($res_atributosMandatory); //função que verifica os campos obrigatorios
                 listarDados(); //lista os dados que são para ser inseridos

             }else{

                 if($_REQUEST["form"]){


                     $_SESSION["form_id"] = $_REQUEST["form"];

                     $form = execute_query("Select *
                                      FROM custom_form
                                          where custom_form.id = '{$_SESSION["form_id"]}'");
                     $form_LINHA = mysqli_fetch_assoc($form);

                     $_SESSION["form_name"] = $form_LINHA["name"];

                        echo ' <h3>   Inserção de valores - '.$_SESSION["form_name"].' - validar </h3>';

                      $atributosMandatory = ("select attribute.name , attribute.id 
                                              FROM attribute,custom_form_has_attribute,custom_form
                                                    WHERE custom_form_has_attribute.custom_form_id = custom_form.id AND attribute.id = custom_form_has_attribute.attribute_id
                                                    AND custom_form.id = '{$_SESSION["form_id"]}'
                                                    AND attribute.state = 'active' AND attribute.mandatory = '1'");

                      $res_atributosMandatory = execute_query ($atributosMandatory);
                      campoObrigatorio($res_atributosMandatory);

                      listarDadosForm();


                 }

             }
             break;
            case "inserir":  // se a varivael $est for igual a inserir
            if($_REQUEST["obj"]) //Se for um objeto
            {


                $_SESSION["obj_id"] = $_REQUEST["obj"]; //guardamos a informação do objeto numa variavel de sessão

                 $obj = execute_query("Select object.name,object.obj_type_id from object Where id = '{$_SESSION["obj_id"]}'");
                 $obj_LINHA = mysqli_fetch_assoc($obj);

                 $_SESSION["obj_name"] = $obj_LINHA["name"];

                 //echo "{$_SESSION["obj_name"]}";

                 echo '<h3>   <b> <br> Inserção de valores - ' . $_SESSION["obj_name"] . ' - Inserção  </br> </b> </h3>';


                //vai buscar o id do atributo obj_ref do objeto
                $atributoObj_ref = "select attribute.id, attribute.obj_fk_id
                                     from attribute,object
                                      where attribute.obj_id = object.id and attribute.obj_id = '.{$_SESSION["obj_id"]}.' and attribute.state = 'active' 
                                      and attribute.value_type = 'obj_ref' LIMIT 1";

                $res_atributoObj_ref = execute_query($atributoObj_ref); //executa a query

                $res_atributoObj_ref_LINHA = mysqli_fetch_assoc($res_atributoObj_ref);


              Transacao(); //Ira ser inciada a transação das insercções



                if($res_atributoObj_ref_LINHA) //caso o objeto tenha algum obj_ref
                    //
                {
                    $_SESSION["obj_inst_id"] = (mysqli_fetch_assoc(execute_query("select obj_inst.id
                                                    from obj_inst
                                                      where object_id = '{$res_atributoObj_ref_LINHA["obj_fk_id"]}'
                                                        and object_name = '{$_SESSION["valoresPreenchidos"][($res_atributoObj_ref_LINHA["id"])]["value"]}'
                                                       LIMIT 1")))["id"];

                }else{
                    //
                    $_SESSION["obj_inst_id"] = (mysqli_fetch_assoc(execute_query("SELECT max(obj_inst.id) as max
															FROM obj_inst")))["max"] + 1;

                    Transacao_query("INSERT INTO obj_inst (id,object_id,object_name)
							VALUES ('{$_SESSION["obj_inst_id"]}','{$_SESSION["obj_id"]}','{$_SESSION["obj_name"]}')");

                }

                  inserir_valores(); //função para inserir valores

            Transacao_fim();
            }else {
                if($_REQUEST["form"]){



                   $_SESSION["form_id"] = $_REQUEST["form"];

                     $form = execute_query("Select *
                                      FROM custom_form
                                          where custom_form.id = '{$_SESSION["form_id"]}'");
                     $form_LINHA = mysqli_fetch_assoc($form);

                     $_SESSION["form_name"] = $form_LINHA["name"];

                        echo ' <h3>   Inserção de valores - '.$_SESSION["form_name"].' - Inserir </h3>';

                        //ids dos atributos obj_ref do formulario
                      $atributos_obj_ref_form_ =  "select attribute.id, attribute.obj_fk_id
                                                        from custom_form_has_attribute,attribute
                                                          WHERE attribute.id = custom_form_has_attribute.attribute_id
                                                          and custom_form_has_attribute.custom_form_id = '{$_SESSION["form_id"]}'
                                                          and attribute.value_type='obj_ref'
                                                          AND attribute.state = 'active' LIMIT 1";

                      $res_atributos_obj_ref_form_= execute_query($atributos_obj_ref_form_);

                      $res_atributos_obj_ref_form_LINHA = mysqli_fetch_assoc($res_atributos_obj_ref_form_);


                      Transacao();

                      if($res_atributos_obj_ref_form_LINHA){ //Se o form ja tiver um obj_ref , vai ser inseridos na instancia do obj_ref

                           $_SESSION["obj_inst_id"] = (mysqli_fetch_assoc(execute_query("select obj_inst.id
                                                    from obj_inst
                                                      where object_id = '{$res_atributos_obj_ref_form_LINHA["obj_fk_id"]}'
                                                        and object_name = '{$_SESSION["valoresPreenchidos"][($res_atributos_obj_ref_form_LINHA["id"])]["value"]}'
                                                       LIMIT 1")))["id"];

                      }else{ // sera iniciada uma nova instancia


                          $_SESSION["obj_inst_id"] = (mysqli_fetch_assoc(execute_query("SELECT max(obj_inst.id) as max
															FROM obj_inst")))["max"] + 1;


                          $id_objecto = mysqli_fetch_assoc(execute_query("SELECT attribute.obj_id as object_id
																			FROM custom_form_has_attribute,attribute
																			WHERE custom_form_has_attribute.attribute_id=attribute.id
																			AND custom_form_has_attribute.custom_form_id = '{$_SESSION["form_id"]}'
																			AND attribute.state='active'
																			 LIMIT 1"))["object_id"];

                            Transacao_query("INSERT INTO obj_inst (id,object_id,object_name)
							VALUES ('{$_SESSION["obj_inst_id"]}','{$id_objecto}','{$_SESSION["form_name"]}')");

                      }

                        inserir_valores();

                        Transacao_fim();


                }

            }

        }


}else {

    printf ("Não tem autorização para aceder a esta página");


}

//#*************************||  #####  #   #  ###   #  #####  ######  ######  #########  ||***************************************************************************
//#*************************||  #      #   #  #  #  #  #      #    #  #       ##         ||***************************************************************************
//#*************************||  ###    #   #  #  #  #  #      #    #  ####       ##      ||***************************************************************************
//#*************************||  #      #   #  #   # #  #      #    #  #             ##   ||***************************************************************************
//#*************************||  #      #####  #    ##  #####  ######  ######  #########   ||***************************************************************************


function listaDeObjetos() {
    $tipoDeObjetos = "Select obj_type.id, obj_type.name From obj_type "; //Query que vai buscar a tabela obj_type o seu id e nome
    $resultTDO = execute_query($tipoDeObjetos);  //Guarda o resultado da query na variavel $resultTDO
    $NR_TDO = mysqli_num_rows($resultTDO);//Guarda em $NR_TDO o numero de tuplos que o resultado da query tem

    echo '<ul>
                     <li> <b> Objetos: </b> </li> ';


    for($i = 0 ; $i < $NR_TDO ;$i++ ){  //Vai precorrer todos os tipos do objeto

     $TDO_LINHA = mysqli_fetch_assoc($resultTDO); //Vai buscar tipo a tipo

        echo  '<ul>
                        <li>  '. $TDO_LINHA["name"] .' </li>';  //

                        echo '<ul>';

                            $TDO_objetos = ("Select distinct object.id,object.name From object Where obj_type_id = {$TDO_LINHA["id"]}"); // Query que vai buscar os objetos desse tipo

                            $res_TDO_objetos = execute_query($TDO_objetos); // executa a query anterior

                            $NR_TDO_objetos =   mysqli_num_rows($res_TDO_objetos); // vai contar todos os tuplos


                            for($j=0; $j < $NR_TDO_objetos; $j++ ){ // vai percorrer todos os objetos desse tipo

                                $TDO_objetos_LINHA = mysqli_fetch_assoc($res_TDO_objetos); // Vai buscar tuplo a tuplo e guarda no array associativo

                                echo'   <li> ';

                                blueButton("insercao-de-valores?estado=introducao&obj={$TDO_objetos_LINHA["id"]}", "[ {$TDO_objetos_LINHA["name"]} ]" );



                                echo '</li> ';

                            }

                        echo  '</ul>';
        echo ' </ul> ';

    }
    echo ' </ul> ';

}

function listaDeFormularios() {

    echo '<ul>';

                echo '<li> <b> Formulários Customizados: </b> </li>';

                $formularios = "SELECT * FROM custom_form";
                $res_formularios = execute_query($formularios);

                $NR_res_formularios = mysqli_num_rows($res_formularios);


        echo '<ul>';
                for($i = 0; $i < $NR_res_formularios; $i++){

                    $formularios_LINHA = mysqli_fetch_assoc($res_formularios);

                    echo ' <li>';

                    blueButton("insercao-de-valores?estado=introducao&form={$formularios_LINHA["id"]}", "[ {$formularios_LINHA["name"]} ]");

                    echo' </li>';

                }
        echo '</ul>';
    echo '</ul>';
}

function listaAtributosDoObj()
{

    $name =  "obj_type_ {$_SESSION["obj_type_id"]}_obj_ {$_SESSION["obj_id"]}";

    ?> <form  method="post"
              onsubmit=" return validateForm()"
              action=<?php echo "?estado=validar&obj={$_SESSION["obj_id"]}"; ?>
              name=<?php echo $name ?>

        >
    <?php

        //query para ir buscar os atributos de um crto objeto
    $atributos_Ass_Obj = ("SELECT  attribute.id, attribute.name, attribute.obj_id, attribute.value_type, attribute.form_field_type, attr_unit_type.name as unit 
                            FROM attribute 
                            LEFT JOIN attr_unit_type ON (attribute.unit_type_id = attr_unit_type.id),object 
                            WHERE attribute.obj_id = '{$_SESSION["obj_id"]}' and attribute.state = 'active' and attribute.obj_id = object.id");

    $res_atributos_Ass_Obj = execute_query($atributos_Ass_Obj); //executa a query

    $NR_res_atributos_Ass_Obj = mysqli_num_rows($res_atributos_Ass_Obj); //guarda numa variavel o numero de tuplos que a query tem

    for($i = 0; $i < $NR_res_atributos_Ass_Obj; $i++){ // precorrer esses atributos desse objeto

        $res_atributos_Ass_Obj_LINHA = mysqli_fetch_assoc($res_atributos_Ass_Obj);  // cria um array associativo com  primeiro tuplo

		$id_res_atributos_Ass_Obj_LINHA = $res_atributos_Ass_Obj_LINHA["id"]; //vai buscar o id desse atributo

        switch($res_atributos_Ass_Obj_LINHA["value_type"]){

            case "text": //caso o value_type = text

                echo  '<br> '.$res_atributos_Ass_Obj_LINHA["name"].' </br>';

                if ($res_atributos_Ass_Obj_LINHA["form_field_type"] = "text" ){ //se o form field type for text

                   
                ?>  <input  type = "text" name =<?php echo $res_atributos_Ass_Obj_LINHA["id"]; ?> autocomplete="off">

                    <?php
                           echo $res_atributos_Ass_Obj_LINHA["unit"]; //
                            echo '<br> <br>';
                            break;
                }
                else{
                      if ($res_atributos_Ass_Obj_LINHA["form_field_type"] = "textbox" ){
                    ?>  <input  type = "text" name =<?php echo $res_atributos_Ass_Obj_LINHA["id"]; ?> autocomplete="off">

                    <?php
                           echo $res_atributos_Ass_Obj_LINHA["unit"]; //
                            echo '<br> <br>';
                            break;
                      }
                }

            case "bool": // caso o value_type = bool

                 echo "Quer adicionar?";
                 echo '<br>';

                ?> <input type = "radio" name =<?php echo $res_atributos_Ass_Obj_LINHA["id"]; ?> autocomplete="off"
                          value = "<?php $res_atributos_Ass_Obj_LINHA["value"]; ?> " >
                <?php
                    echo $res_atributos_Ass_Obj_LINHA["name"],$res_atributos_Ass_Obj_LINHA["unit"]; //mostra o nome do atributo e a unidade

                 ?> <br> <?php
                break;

            case "int":
            case "double":

                 echo  '<br> '.$res_atributos_Ass_Obj_LINHA["name"].' </br>'; //mostra o nome do atributo
            ?>  <input  type = "text" name =<?php echo $res_atributos_Ass_Obj_LINHA["id"]; ?> autocomplete="off">

            <?php

                      echo $res_atributos_Ass_Obj_LINHA["unit"];
                      echo '<br> <br>';
                      break;

            case "enum":

                $opccoes_allowed_value = ("SELECT attr_allowed_value.id, attr_allowed_value.value
                                          from attr_allowed_value , attribute
                                          where attr_allowed_value.attribute_id = attribute.id 
                                          AND attr_allowed_value.attribute_id = '{$res_atributos_Ass_Obj_LINHA["id"]}' ") ; //Query que vai buscar as opcoes a tabela allowed value de cada atributo


                $res_opccoes_allowed_value = execute_query($opccoes_allowed_value); //executa a query

                $NR_res_opccoes_allowed_value = mysqli_num_rows($res_opccoes_allowed_value); // ve quantos tuplos esa query tem

                echo  '<br> '.$res_atributos_Ass_Obj_LINHA["name"].' : </br>'; // o nome do atributo que tem um value type = enum

                switch ($res_atributos_Ass_Obj_LINHA["form_field_type"]){  //dependendo do tipo campo que estiver especificado na BD em form_field_type  (radio,checkbox,selectbox)

                    case "radio": // se o tipo de campo for radio

                        for ($j = 0; $j < $NR_res_opccoes_allowed_value; $j++ ) {
                            $res_opccoes_allowed_value_LINHA = mysqli_fetch_assoc($res_opccoes_allowed_value);

                            ?>  <input type ="radio" name = <?php echo $res_atributos_Ass_Obj_LINHA["id"]; ?>   autocomplete="off"
                                       value = "<?php echo $res_opccoes_allowed_value_LINHA["value"]; ?> " >
                            <?php
                                echo  "{$res_opccoes_allowed_value_LINHA["value"]} {$res_atributos_Ass_Obj_LINHA["unit"]}" ;

                            ?> <br><?php
                        }
                            break;

                    case "checkbox": // se o tipo de campo for checkbox

                        for ($k = 0; $k < $NR_res_opccoes_allowed_value; $k++) {  //vai percorrer

                            $res_opccoes_allowed_value_LINHA = mysqli_fetch_assoc($res_opccoes_allowed_value);
                            ?>
                            <input type = "checkbox" name = <?php echo $res_atributos_Ass_Obj_LINHA["id"]; ?>  autocomplete="off"
                                   value = " <?php echo $res_opccoes_allowed_value_LINHA["value"]  ?>" >
                            <?php
                                echo  "{$res_opccoes_allowed_value_LINHA["value"]} {$res_atributos_Ass_Obj_LINHA["unit"]}" ;
                           ?> <br><?php
                        }
                             break;
                            echo '<br>';

                    case "selectbox": // se o tipo de campo for selectbox

                        ?>

                        <select name = <?php echo $res_atributos_Ass_Obj_LINHA["id"]; ?>  >

                         <?php

                        for ($l = 0; $l < $NR_res_opccoes_allowed_value; $l++) { //precorre todas as opçoes que tem no campo form_field_type = selectbox

                            $res_opccoes_allowed_value_LINHA = mysqli_fetch_assoc($res_opccoes_allowed_value);
                            ?>
                               <option  value = " <?php echo $res_opccoes_allowed_value_LINHA["value"];  ?>" >
                            <?php

                            echo  "{$res_opccoes_allowed_value_LINHA["value"]} {$res_atributos_Ass_Obj_LINHA["unit"]}" ;

                        }
                        echo '</select> <br> ';
                        break;

                }
                break;

             case "obj_ref":  //caso o value_type seja obj_ref

            $opccoes_obj_ref = ("SELECT obj_inst.id , obj_inst.object_name
                                          FROM attribute, obj_inst
                                          WHERE attribute.obj_fk_id = obj_inst.object_id AND attribute.obj_id='{$_SESSION["obj_id"]}'"); //query que vai buscar todas as opcçoes de instancia de um objeto a tabela obj_inst

            $res_opccoes_obj_ref = execute_query($opccoes_obj_ref); //executa a query

            $NR_opccoes_obj_ref = mysqli_num_rows($res_opccoes_obj_ref); //numero de opccoes

            echo $res_atributos_Ass_Obj_LINHA["name"] ; //mostra o nome do atrributo

            ?>  <select name = <?php echo $res_atributos_Ass_Obj_LINHA["id"]; ?>  >
            <?php

            for($h = 0; $h < $NR_opccoes_obj_ref; $h++)  //ira precorrer
            {
            $res_opccoes_obj_ref_LINHA = mysqli_fetch_assoc($res_opccoes_obj_ref);

            ?>
            <option value = " <?php echo $res_opccoes_obj_ref_LINHA["id"] .','. $res_opccoes_obj_ref_LINHA["object_name"];  ?>" >
                <?php
                echo "{$res_opccoes_obj_ref_LINHA["object_name"]}"; //mostra no selectbox o nome dos
                ?> <br><?php
                }
                ?> </select><br> <?php
            break;

            case "Default":

                echo "Deu merda";

                break;
        }


        $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["name"] = $res_atributos_Ass_Obj_LINHA["name"];              //guarda a informação
        $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["unit"] = $res_atributos_Ass_Obj_LINHA["unit"];               //guarda a informação
        $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["value_type"] =$res_atributos_Ass_Obj_LINHA["value_type"];    //guarda a infomação


    }
    ?>
    <input type=hidden value="validar" name="estado">
    <input type=submit value="Submeter" name="submit">
    <?php

    echo '</form>';
}

function listaAtributosDoForm(){

$name =  "form_ {$_SESSION["form_id"]}";

    ?> <form  method="post"
              action=<?php echo "?estado=validar&form={$_SESSION["form_id"]}"; ?>
              name=<?php echo $name ?>
        >
    <?php

  $atributosDoForm = "SELECT attribute.id, attribute.name,attribute.form_field_type,attribute.value_type,attr_unit_type.name as unit , attribute.obj_id,attribute.name as atributo
                            FROM attribute LEFT JOIN attr_unit_type
                            ON(attribute.unit_type_id = attr_unit_type.id),custom_form_has_attribute,custom_form,object
                            WHERE attribute.id = custom_form_has_attribute.attribute_id
                            AND custom_form_has_attribute.custom_form_id = custom_form.id
                            AND attribute.obj_id = object.id
                            AND custom_form.id = '{$_SESSION["form_id"]}'
                            AND attribute.state = 'active'";

  $res_atributosDoForm = execute_query($atributosDoForm);

  $NR_res_atributosDoForm = mysqli_num_rows($res_atributosDoForm);



  if(!$NR_res_atributosDoForm){

      echo "Este formulario não possui atributos. " ;
      echo '<br>' ;

  }
  for ($i = 0; $i < $NR_res_atributosDoForm; $i++){

       $res_atributosDoForm_LINHA = mysqli_fetch_assoc($res_atributosDoForm);

       $id_res_atributos_Ass_Obj_LINHA = $res_atributosDoForm_LINHA["id"];

       switch($res_atributosDoForm_LINHA["value_type"]){

            case "text": //caso o value_type = text

                echo  '<br> '.$res_atributosDoForm_LINHA["name"].' </br>';

                if ($res_atributosDoForm_LINHA["form_field_type"] = "text" ){ //se o form field type for text


                ?>  <input  type = "text" name =<?php echo $res_atributosDoForm_LINHA["id"]; ?> autocomplete="off">

                    <?php
                           echo $res_atributosDoForm_LINHA["unit"]; //
                            echo '<br> <br>';
                            break;
                }

            case "bool": // caso o value_type = bool

                echo "Quer adicionar?";
                echo '<br>';


                ?> <input type = "radio" name =<?php echo $res_atributosDoForm_LINHA["id"]; ?> autocomplete="off"
                          value = "<?php $res_atributosDoForm_LINHA["value"]; ?> " >
                <?php
                    echo $res_atributosDoForm_LINHA["name"]," ", $res_atributosDoForm_LINHA["unit"]; //mostra o nome do atributo e a unidade
                echo '<br> <br>';
                break;

            case "int":
            case "double":

                 echo  '<br> '.$res_atributosDoForm_LINHA["name"].' </br>'; //mostra o nome do atributo
            ?>  <input  type = "text" name =<?php echo $res_atributosDoForm_LINHA["id"]; ?> autocomplete="off">

            <?php

                      echo $res_atributosDoForm_LINHA["unit"];
                      echo '<br> <br>';
                      break;

            case "enum":

                $opccoes_allowed_value = ("SELECT attr_allowed_value.id,attr_allowed_value.value
													FROM attribute,attr_allowed_value 
													WHERE attr_allowed_value.attribute_id = attribute.id
													AND attr_allowed_value.attribute_id=' {$res_atributosDoForm_LINHA["id"]} '") ; //Query que vai buscar as opcoes a tabela allowed value de cada atributo

                $res_opccoes_allowed_value = execute_query($opccoes_allowed_value); //executa a query

                $NR_res_opccoes_allowed_value = mysqli_num_rows($res_opccoes_allowed_value); // ve quantos tuplos esa query tem

                echo  '<br> '.$res_atributosDoForm_LINHA["name"].' : </br>'; // o nome do atributo que tem um value type = enum

                switch ($res_atributosDoForm_LINHA["form_field_type"]){  //dependendo do tipo campo que estiver especificado na BD em form_field_type  (radio,checkbox,selectbox)

                    case "radio": // se o tipo de campo for radio
                        for ($j = 0; $j < $NR_res_opccoes_allowed_value; $j++ ) {

                            $res_opccoes_allowed_value_LINHA = mysqli_fetch_assoc($res_opccoes_allowed_value);

                            ?>  <input type ="radio" name = <?php echo $res_atributosDoForm_LINHA["id"]; ?>   autocomplete="off"
                                       value = "<?php echo $res_opccoes_allowed_value_LINHA["value"]; ?> " >
                            <?php
                                echo  "{$res_opccoes_allowed_value_LINHA["value"]} {$res_atributosDoForm_LINHA["unit"]}" ;

                            echo '<br>';
                        }
                        echo '<br>';
                            break;

                    case "checkbox": // se o tipo de campo for checkbox

                        for ($k = 0; $k < $NR_res_opccoes_allowed_value; $k++) {  //vai percorrer

                            $res_opccoes_allowed_value_LINHA = mysqli_fetch_assoc($res_opccoes_allowed_value);
                            ?>
                            <input type = "checkbox" name = <?php echo $res_atributosDoForm_LINHA["id"]; ?>  autocomplete="off"
                                   value = " <?php echo $res_opccoes_allowed_value_LINHA["value"]  ?>" >
                            <?php
                                echo  "{$res_opccoes_allowed_value_LINHA["value"]} {$res_atributosDoForm_LINHA["unit"]}" ;
                            echo '<br>';
                        }
                             break;

                    case "selectbox": // se o tipo de campo for selectbox

                        ?>

                        <select name = <?php echo $res_atributosDoForm_LINHA["id"]; ?>  >

                         <?php

                        for ($l = 0; $l < $NR_res_opccoes_allowed_value; $l++) { //precorre todas as opçoes que tem no campo form_field_type = selectbox

                            $res_opccoes_allowed_value_LINHA = mysqli_fetch_assoc($res_opccoes_allowed_value);
                            ?>
                               <option  value = " <?php echo $res_opccoes_allowed_value_LINHA["value"];  ?>" >
                            <?php

                            echo  "{$res_opccoes_allowed_value_LINHA["value"]} {$res_atributosDoForm_LINHA["unit"]}" ;

                        }
                        echo '</select> <br> ';
                        break;
                }
                break;

             case "obj_ref":  //caso o value_type seja obj_ref

            $opccoes_obj_ref = ("SELECT obj_inst.id as obj_inst_id , obj_inst.object_name 
													FROM custom_form,custom_form_has_attribute,attribute,obj_inst
													WHERE custom_form.id=custom_form_has_attribute.custom_form_id
													AND attribute.id=custom_form_has_attribute.attribute_id
													AND obj_inst.object_id  = attribute.obj_fk_id
													AND custom_form.id='{$_SESSION["form_id"]}'"); //query que vai buscar todas as opcçoes de instancia de um objeto a tabela obj_inst

            $res_opccoes_obj_ref = execute_query($opccoes_obj_ref); //executa a query

            $NR_opccoes_obj_ref = mysqli_num_rows($res_opccoes_obj_ref); //numero de opccoes

            echo $res_atributosDoForm_LINHA["name"] ; //mostra o nome do atrributo

            ?>  <select name = <?php echo $res_atributosDoForm_LINHA["id"]; ?>  >
            <?php

            for($h = 0; $h < $NR_opccoes_obj_ref; $h++)  //ira precorrer
            {
            $res_opccoes_obj_ref_LINHA = mysqli_fetch_assoc($res_opccoes_obj_ref);

            ?>
            <option value = " <?php echo $res_opccoes_obj_ref_LINHA["id"] .','. $res_opccoes_obj_ref_LINHA["object_name"];  ?>" >
                <?php
                echo "{$res_opccoes_obj_ref_LINHA["object_name"]}"; //mostra no selectbox o nome dos
                ?> <br><?php
                }
                ?> </select><br> <?php
            break;

             case "Default":

                echo "Deu merda";

                break;
        }

         $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["name"] = $res_atributosDoForm_LINHA["name"];              //guarda a informação
         $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["unit"] = $res_atributosDoForm_LINHA["unit"];               //guarda a informação
         $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["value_type"] =$res_atributosDoForm_LINHA["value_type"];    //guarda a infomação
  }

 ?>
    <input type=hidden value="validar" name="estado">
    <input type=submit value="Submeter" name="submit">
    <br>
    <?php

    echo '</form>';
}

function campoObrigatorio($res_atributosMandatory)
{
    $NR_res_atributosMandatory = mysqli_num_rows($res_atributosMandatory); //devolve o numero de atributos mandatory

    for($i = 0; $i < $NR_res_atributosMandatory; $i++ ){ //vai percorrer todos os atributos mandatory

        $NR_res_atributosMandatory_LINHA = mysqli_fetch_assoc($res_atributosMandatory); //vai verificar linha

        if(!$_POST["{$NR_res_atributosMandatory_LINHA["id"]}"]){


            echo ("Falta preencher campos" );
            echo '.<br>.';
            backButton("Voltar atrás");
            die(); // se o if se verificar acaba e nao se repete mais no for
        }
    }
}

function listarDados()
{

    echo "Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão corretos e que pretende submeter os mesmos?<br><br>";

        if($_SESSION["obj_id"]) //Se for um objeto que estamos a tentar inserir
        {
           // echo "if";
            $atributos_Objeto = ("select attribute.id
                                    from attribute,object
                                        where object.id = attribute.obj_id and object.id = '{$_SESSION["obj_id"]}' and attribute.state = 'active' "); //vai buscar os id's dos atributos de um certo objeto

            $res_atributos_Objeto = execute_query($atributos_Objeto); //executa a query

            $NR_res_atributos_Objeto = mysqli_num_rows($res_atributos_Objeto); // numero de atributos que um objeto tem

           for($i = 0; $i < $NR_res_atributos_Objeto; $i++ ){ //precorre todos esses atributos
           // echo " for1 ";
               $id_res_atributos_Ass_Obj_LINHA = mysqli_fetch_assoc($res_atributos_Objeto)["id"]; //vai buscar o id de um dos atributos

               switch ($_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["value_type"]){ //Se o value_type for

                   case "bool":
                   // echo "bool";
                       if(isset($_POST[$id_res_atributos_Ass_Obj_LINHA])) //se a variavel nao for null
                       {
                           $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["value"] = "sim"; //guarda no variavel de sessao value a string


                       }else{
                           $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["value"] = "não"; //guarda no variavel de sessao value a string
                       }
                    break;

                   case "obj_ref":  // se for uma chv estrangeira
                          $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["value"] = (explode(",",$_POST[$id_res_atributos_Ass_Obj_LINHA]))[1];

                   break;

                   default: // se for outro caso
                    //   echo"default";
                       $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["value"] = $_POST[$id_res_atributos_Ass_Obj_LINHA];

                       break;
               }

           }

            $res_atributos_Objeto = execute_query($atributos_Objeto); //volto a executar a query para "reniciar o precorrer"

            $NR_res_atributos_Objeto = mysqli_num_rows($res_atributos_Objeto);  //numero de atributos

            for($j = 0; $j < $NR_res_atributos_Objeto; $j++ ){ //precorro os atributos de um objeto
           // echo "for2";

                $id_res_atributos_Ass_Obj_LINHA = mysqli_fetch_assoc($res_atributos_Objeto)["id"]; //vai buscar um id de um dos atributos


            //mostra o nome do atributo
            //mostra o que foi inserido pelo utilizador
            //mostra a unidade
                echo "{$_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["name"]}:   
                      {$_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["value"]}   
                      {$_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["unit"]}    
                      <br>
                ";

               // var_dump($_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]) ;
                 ?> <br><?php
            }
        }
        //botão para passar para o estado inserir
                ?> <form method="post" action=<?php echo "?estado=inserir&obj={$_SESSION["obj_id"]}"; ?>
				name=<?php echo "obj_type_ {$_SESSION["obj_type_id"]}_obj_ {$_SESSION["obj_id"]}"; ?> >
								<input type=submit value="Submeter" name="submit" >
					</form> <?php

				 echo '<br>';
		         backButton("Voltar atrás");



}

function listarDadosForm(){

    echo "Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão corretos e que pretende submeter os mesmos?<br><br>";

    if ($_SESSION["form_id"]) {

        //Buscar os atributos associados a esse formulario
           $atributos_FORM = ("Select attribute.id
                            from attribute,custom_form_has_attribute
                              where attribute.id = custom_form_has_attribute.attribute_id
                               and  custom_form_has_attribute.custom_form_id = '{$_SESSION["form_id"]}' and attribute.state = 'active'");


            $res_atributos_FORM = execute_query($atributos_FORM); //executa a query

            $NR_res_atributos_FORM = mysqli_num_rows($res_atributos_FORM); // numero de atributos que um formulario tem

           for($i = 0; $i < $NR_res_atributos_FORM; $i++ ){ //precorre todos esses atributos
           // echo " for1 ";
               $id_res_atributos_Ass_FORM_LINHA = mysqli_fetch_assoc($res_atributos_FORM)["id"]; //vai buscar o id de um dos atributos

               switch ($_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_FORM_LINHA]["value_type"]){ //Se o value_type for

                   case "bool":
                   // echo "bool";
                       if(isset($_POST[$id_res_atributos_Ass_FORM_LINHA])) //se a variavel nao for null
                       {
                           $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_FORM_LINHA]["value"] = "sim"; //guarda no variavel de sessao value a string


                       }else{
                           $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_FORM_LINHA]["value"] = "não"; //guarda no variavel de sessao value a string
                       }
                    break;

                   case "obj_ref":  // se for uma chv estrangeira
                          $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_FORM_LINHA]["value"] = (explode(",",$_POST[$id_res_atributos_Ass_FORM_LINHA]))[1];

                   break;

                   default: // se for outro caso
                    //   echo"default";
                       $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_FORM_LINHA]["value"] = $_POST[$id_res_atributos_Ass_FORM_LINHA]; //vai buscar o valor guarddao no form pelo post com o id

                       break;
               }

           }
              $res_atributos_FORM = execute_query($atributos_FORM); //volto a executar a query para "reniciar o precorrer"

            $NR_res_atributos_FORM = mysqli_num_rows($res_atributos_FORM);  //numero de atributos

            for($j = 0; $j < $NR_res_atributos_FORM; $j++ ){ //precorro os atributos de um FORM
           // echo "for2";

                $id_res_atributos_Ass_FORM_LINHA = mysqli_fetch_assoc($res_atributos_FORM)["id"]; //vai buscar um id de um dos atributos


            //mostra o nome do atributo
            //mostra o que foi inserido pelo utilizador
            //mostra a unidade
                echo "{$_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_FORM_LINHA]["name"]}:   
                      {$_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_FORM_LINHA]["value"]}   
                      {$_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_FORM_LINHA]["unit"]}    
                      <br>
                ";

               // var_dump($_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]) ;
                 ?> <br><?php
            }

            //botão para passar para o estado inserir
             ?> <form method="post" action=<?php echo "?estado=inserir&form={$_SESSION["form_id"]}"; ?>
				name=<?php echo "form_ {$_SESSION["form_id"]}"; ?> >
				<input type=submit value="Submeter" name="submit" >
				</form> <?php

        }

               echo '<br>';
               backButton("Voltar atrás");
}

function inserir($obj_inst_id,$attribute_id,$value){

    // vamos buscar o nome do utilizador  no wordpress
    $current_user_name = (wp_get_current_user())->user_login;

    //o valor é inserido na base de dados
    Transacao_query("INSERT INTO value (obj_inst_id,attr_id,value,date,time,producer) 
				VALUES ('$obj_inst_id', '{$attribute_id}','{$value}',CURDATE(),CURTIME(),'{$current_user_name}')");


}

function inserir_valores(){

if($_SESSION["obj_id"]){
      $atributos_Objeto = ("select attribute.id
                                    from attribute
                                        where attribute.obj_id = '{$_SESSION["obj_id"]}' and attribute.state = 'active' ");

       $res_atributos_Objeto = execute_query($atributos_Objeto); //Vaos buscar o id dos atributos que queremos isnerir de um certo objeto

       $NR_res_atributos_Objeto = mysqli_num_rows($res_atributos_Objeto); //vemos o numero de atributos que um objeto tem

       for($i= 0; $i < $NR_res_atributos_Objeto; $i++) { //precorremos os atributos de um objeto

           $id_res_atributos_Ass_Obj_LINHA = mysqli_fetch_assoc($res_atributos_Objeto)["id"]; //vamos buscar um atributo


            inserir($_SESSION["obj_inst_id"],$id_res_atributos_Ass_Obj_LINHA, $_SESSION["valoresPreenchidos"][$id_res_atributos_Ass_Obj_LINHA]["value"]); //inserimos o value desse atributo na BD

       }

       echo "<i>Inseriu o(s) valor(es) com sucesso.</i><br><br>";
       backButton("Voltar atrás");
       echo '<br><br>';
       blueButton("insercao-de-valores?estado=introducao&obj={$_SESSION["obj_id"]}","Continuar a inserir valores neste objeto");

        }else{

            if($_SESSION["form_id"]){

                //vai buscar  o id do atributo dos formularios
                $atributos_Form = execute_query("SELECT attribute.id
                                                    FROM custom_form_has_attribute,attribute
                                                       where custom_form_has_attribute.custom_form_id = '{$_SESSION["form_id"]}'
                                                       and attribute.state = 'active'
                                                       and custom_form_has_attribute.attribute_id = attribute.id");

                $NR_atributos_Form = mysqli_num_rows($atributos_Form);

                for($t= 0; $t < $NR_atributos_Form; $t++) {

                    $atributos_Form_LINHA = mysqli_fetch_assoc($atributos_Form)["id"];


                     inserir($_SESSION["obj_inst_id"],$atributos_Form_LINHA,$_SESSION["valoresPreenchidos"][$atributos_Form_LINHA]["value"]);

                }

                echo "<i>Inseriu o(s) valor(es) com sucesso.</i><br><br>";
                    backButton("Voltar atrás");
                     echo '<br><br>';
                    blueButton("insercao-de-valores?estado=introducao&form={$_SESSION["form_id"]}","Continuar a inserir valores neste objeto");

            }

        }
}


