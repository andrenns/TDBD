<?php
require_once("custom/php/common.php");

echo '<script src="/custom/js/validations.js"></script>';

//se o utilizador tiver efetuado log in e tiver a capability manage allowed values
if(is_user_logged_in() and current_user_can("dynamic_search"))
{
	//obtem o estado de execução
	$exec_state=$_REQUEST["estado"];	

	//se não estiver num estado de execução especifico 
	if(!$exec_state)
	{
		//subtitulo caso não haja estado de execução especifico
		echo '<h3> <b> <i>  Pesquisa Dinâmica - escolher objeto </i> </b></h3>';
		
		//retorna os tipos de objetos
		$obj_type = execute_query("SELECT obj_type.name, id 
						FROM obj_type");
		
		//numero de tipos de objetos
		$nr_obj_type = mysqli_num_rows($obj_type);

		//inicia a listagens dos objetos
		echo '<ul> <li> <b> <i> Objectos: </i> </b> </li> <ul> ';

		//para cada tipo de objeto lista o seu nome 
		for($i = 0; $i<$nr_obj_type; $i++)
		{
			//retorna a linha do tipo de objeto 'atual'
			$row_type = mysqli_fetch_assoc($obj_type);

			//retorna os objetos de um certo tipo que sao referenciados pelo menos uma vez em obj_fk_id
			$obj_ref_obj_fk_id = execute_query("SELECT DISTINCT object.name, object.id 
								FROM object,attribute
								WHERE object.obj_type_id = {$row_type["id"]}
								AND attribute.obj_fk_id=object.id
								ORDER BY object.name ASC
								");

			//numero de tuplos devolvidos em $obj_ref_obj_fk_id
			$nr_obj_ref_obj_fk_id =  mysqli_num_rows($obj_ref_obj_fk_id);

			echo '<li>'.$row_type["name"].'</li> <ul>';
			

			//para cada tipo de objeto lista os objetos desse tipo que são referenciados pelo menos uma vez em obj_fk_id
			for($j = 0; $j<$nr_obj_ref_obj_fk_id; $j++)
			{
				//retorna a linha 'atual' do objeto de um certo tipo que é referenciados pelo menos uma vez em obj_fk_id
				$row_obj_ref_obj_fk_id = mysqli_fetch_assoc($obj_ref_obj_fk_id);

				 echo '<li>';
				blueButton("pesquisa-dinamica?estado=escolha&obj={$row_obj_ref_obj_fk_id["id"]}",
						"[{$row_obj_ref_obj_fk_id["name"]}]");
				echo'</li>';
			}
			
			echo '</ul>';	
			//echo '</ul>';		
		}
	echo '</ul> </ul>';	
			
	}
	else
	{	//caso tenha um estado de execução especifico 
		switch($exec_state)	
		{	//caso o estado seja escolha 
			case "escolha": 

				$nr_attr = 0; //guarda numero de atributos para juntar ao nome da checkbox do form
				
				$obj_id=$_REQUEST["obj"];//guarda o valor da variavel obj do url
				$_SESSION["obj_id"]=$obj_id;//guarda o valor da variavel obj do url numa variavel de sessao para ser usado no estado execucao
				//atributos do objeto selecionado
				$attr_obj= execute_query("SELECT DISTINCT name,id
							FROM attribute
							WHERE obj_id={$obj_id}
							");

				//numero de atributos do objeto
				$nr_attr_obj = mysqli_num_rows($attr_obj);
				
				//subtitulo para a escolha do atriutos a aparecer no resultado da pesquisa
				echo '<h3> <b> <i>  Pesquisa Dinâmica - escolher atributos </i> </b></h3>';	
				//inicio do form para apararecer as checkboxes 
				echo '<form method="post" name="pesquisa-dinamica_escolha" onsubmit="return performCSValidationGO(\''.DO_SERVER_SIDE_VALIDATION.'\')">';
				
				//inicia a listagens dos atributos
				echo "<ul> <li> <b> <i> Atributos do objeto escolhido: </i> </b> </li> <ul>";			
				
				unset($_SESSION["attr_id"]);
				

				//para cada atributo escreve o seu nome e mostra uma checkbox a frente do atributo
				for($i=0; $i<$nr_attr_obj;$i++)
				{
					$form_input_name = "attr_checked$nr_attr";
					//linha do atributo 'atual'
					$attr_obj_row = mysqli_fetch_assoc($attr_obj);
					//escreve o atributo 'atual' e uma select box a frente
					echo '<li> '.$attr_obj_row["name"].' <input type="checkbox" name='.$form_input_name.' autocomplete="off" value='.$attr_obj_row["id"].'> </li>';
				$_SESSION["attr_id"][$nr_attr]=$attr_obj_row["id"];
				$nr_attr+=1;
				}
				echo '</ul>';
				
				//objetos que tem pelo menos um atributo com value type obj_ref e obj_fk_id seja o do objeto escolhido
				$obj_ref = execute_query("SELECT DISTINCT object.name, object.id
							FROM attribute, object
							WHERE attribute.value_type='obj_ref'
							AND attribute.obj_fk_id={$obj_id}
							AND object.id=attribute.obj_id");

				//numero de objetos que tem pelo menos um atributo com value type obj_ref e obj_fk_id seja o do objeto escolhido
				$nr_obj_ref = mysqli_num_rows($obj_ref);			
				
				if($nr_obj_ref>0)
				{
				echo '<li> <b> <i> Atributos dos objetos que o objecto escolhido referencia: </i> </b> </li> <ul>';
				}
				
				for($j=0;$j<$nr_obj_ref;$j++)
				{
					//linha do objeto 'atual'
					$obj_ref_row = mysqli_fetch_assoc($obj_ref);
					
					echo '<li> <i>'.$obj_ref_row["name"].': </i> </li> <ul>'; 
					
					//atributos dos objetos cujo atributo obj_fk_id referencia o objeto escolhido
					$attr_obj_ref = execute_query("SELECT attribute.name,attribute.id
									FROM attribute
									WHERE attribute.obj_id= {$obj_ref_row["id"]}
									AND value_type != 'obj_ref'");
					//numero de atributos dos objetos cujo atributo obj_fk_id referencia o objeto escolhido
					$nr_attr_obj_ref = mysqli_num_rows($attr_obj_ref);

					for($k=0;$k<$nr_attr_obj_ref;$k++)
					{
						$form_input_name = "attr_checked$nr_attr";

						//linha do atributo 'atual'
						$attr_obj_ref_row = mysqli_fetch_assoc($attr_obj_ref);				
											
						//escreve o atributo 'atual' e uma select box a frente
						echo '<li> '.$attr_obj_ref_row["name"].' <input type="checkbox" name='.$form_input_name.' autocomplete="off" value='.$attr_obj_ref_row["id"].'></li>';

					$_SESSION["attr_id"][$nr_attr]=$attr_obj_ref_row["id"];
					$nr_attr+=1;

					
					}
				echo '</ul>';
				}
				$_SESSION["nr_attr"]=$nr_attr;
				echo '</ul></ul>';
				echo '<h3> <b> <i>  Pesquisa Dinâmica - escolher trios </i> </b></h3>';	
				echo '<ul>';
				for($i=0;$i<$nr_attr;$i++)
				{
					$selectbox_op= "op$i";
					$selectbox_value = "value$i";
					$selectbox_bool = "bool$i";
					//id do atributo do objeto selecionado ou de um objeto cujo atributo obj_fk_id referencia o objeto escolhido
					$attr_id = $_SESSION["attr_id"][$i];
					//verifica value_type do atributo;
					$value_type_query = execute_query("SELECT attribute.name,value_type,object.name as obj_name
									FROM attribute,object
									WHERE attribute.id = {$attr_id}
									AND object.id=attribute.obj_id");
					
					$value_type_row = mysqli_fetch_assoc($value_type_query);
					//mostra o nome dos objetos mas este if previne que estes se repitam 
					if(strcmp($obj_name,$value_type_row["obj_name"])!=0)
					{
						$obj_name = $value_type_row["obj_name"];
						echo '<li><b><i>'.$obj_name.'</b></i></li>';
					}					
					
					$value_type = $value_type_row["value_type"];
					$nr_operadores = count($operadores);
					switch($value_type)
						{	
							//caso seja enum vai buscar os valores a mostrar a tabela attr_allowed_value
							case "enum":
								
								//valores permitidos do atributo
								$allowed_value = execute_query("SELECT value,id
												FROM attr_allowed_value
												WHERE attribute_id={$attr_id}");
													
								$nr_allowed_value = mysqli_num_rows($allowed_value);
								
								echo $value_type_row["name"];
								echo ': ';
								echo '<select name='.$selectbox_op.'>
									<option value = "vazio"> </option>
									<option value = "0"> '.$operadores[0].' </option>
									<option value = "1"> '.$operadores[1].' </option>
									</select>';
								echo '<select name='.$selectbox_value.'>
									<option value = "vazio"> </option>';
								for($j=0;$j<$nr_allowed_value;$j++)
								{
									$allowed_value_row = mysqli_fetch_assoc($allowed_value);
									echo '<option value='.$allowed_value_row["value"].' >'.$allowed_value_row["value"].'</option>';
								}							
								echo '</select><br><br>';
								break;

							//caso seja bool apresenta option com true ou false
							case "bool":
								echo $value_type_row["name"];
								echo ': ';
								echo '<select name='.$selectbox_bool.'>
									<option value = "vazio"> </option>
									<option value = "1"> Sim </option>
									<option value = "0"> Nao </option>
									</select>';
								echo '<br><br>';
								break;

							//caso seja int apresenta o operadores e espaço para inserir inteiro
							case "int":
								
								echo $value_type_row["name"];
								echo ': ';
								echo '<select name='.$selectbox_op.'>
									<option value = "vazio"> </option>';
									for($k=0;$k<$nr_operadores;$k++)
									{
									echo '<option value = "'.$k.'"> '.$operadores[$k].' </option>';
									}
									echo '</select>';
								
								$fieldName= "int$i";
								echo '<input type="number" name='.$fieldName.' autocomplete="off"><br><br>';
								break;

							//caso seja double apresenta o operadores e espaço para inserir double
							case "double":
								
								echo $value_type_row["name"];
								echo ': ';
								echo '<select name = '.$selectbox_op.'>
									<option value = "vazio"> </option>';
									for($k=0;$k<$nr_operadores;$k++)
									{
									echo '<option value = "'.$k.'"> '.$operadores[$k].' </option>';
									}
									echo '</select>';
								$fieldName= "double$i";
								echo '<input type="number" step="any" name='.$fieldName.' autocomplete="off"><br><br>';

								break;	

							//caso seja text apresenta o espaço para inserir texto
							case "text":
								
								echo $value_type_row["name"];
								echo ': ';
								echo '<select name='.$selectbox_op.'>
									<option value = "vazio"> </option>
									<option value = "0"> '.$operadores[0].' </option>
									<option value = "1"> '.$operadores[1].' </option>
									</select>';
								
								$fieldName= "text$i";
								echo '<input type="text" name='.$fieldName.' autocomplete="off"><br><br>';
								break;			
						}
					
					
				}
				echo '</ul>';
				echo '<input type="hidden" value="execucao" name="estado"><br>
				<input type="submit" value="Escolher Atributos" name="submit"><br>';
				break;	
			case "execucao":
				//nr de atributos do objeto e dos objetos cujo atributo obj_fk_id referencia o objeto escolhido
				$nr_attr=$_SESSION["nr_attr"];
				$name = "";
				//id do objeto selecionado;
				$obj_id = $_SESSION["obj_id"];
				$flag_obj_selected = 0;
				
				//instancias do objeto selecionado
				$obj_inst_id = execute_query("SELECT id
								FROM obj_inst
								WHERE object_id = {$obj_id}");
				//numero de instancias do objeto selecionado
				$nr_obj_inst_id = mysqli_num_rows($obj_inst_id);
				
				$nr_obj_inst = 0;
				$attr_id_flag=0;
				$attr_checked_name=array();
				$attr_checked_id_display=array();
				$obj_inst_id_array=array();
				for($i=0;$i<$nr_obj_inst_id;$i++)
				{
					
					$obj_inst_id_row = mysqli_fetch_assoc($obj_inst_id);
					$obj_inst_id_value = $obj_inst_id_row["id"];
					$query = "SELECT DISTINCT value.obj_inst_id
							FROM value, obj_inst
							WHERE obj_inst.id = {$obj_inst_id_value}
							AND obj_inst.id = obj_inst_id ";

					for($k=0;$k<$nr_attr;$k++)
					{
						$flag_obj_selected = 0;
						$selectbox_op= "op$k";
						$selectbox_value = "value$k";
						$selectbox_bool = "bool$k";
						$name_attr_checkbox = "attr_checked$k";
						$int_fieldName = "int$k";
						$double_fieldName = "double$k";
						$text_fieldName = "text$k";
						$op = $_REQUEST[$selectbox_op];
						$value = $_REQUEST[$selectbox_value];
						$bool = $_REQUEST[$selectbox_bool];
						$int = $_REQUEST[$int_fieldName];
						$double = $_REQUEST[$double_fieldName];
						$text = $_REQUEST[$text_fieldName];
						$attr_id_checked = $_REQUEST[$name_attr_checkbox];
						$attr_id = $_SESSION["attr_id"][$k];
						//se o atributo tiver sido selecionado guarda o nome do atributo
						if(isset($attr_id_checked)&&$attr_id_flag==0)
						{
							
							$attr_checked_name_result = execute_query("SELECT name 
												FROM attribute
												WHERE id = {$attr_id}");
							
							$attr_checked_name_row = mysqli_fetch_assoc($attr_checked_name_result);
							array_push($attr_checked_name,$attr_checked_name_row["name"]);
							array_push($attr_checked_id_display,$attr_id);
						
						}
						//retorna um tuplo se o atributo 'atual' pertence ao objeto escolhido
						$attr_obj_selected = execute_query("SELECT id
										FROM attribute
										WHERE obj_id = {$obj_id}
										AND id = {$attr_id}");
						
						$nr_attr_obj_selected = mysqli_num_rows($attr_obj_selected);					
						
						//se for atributo do objeto selecionado
						if($nr_attr_obj_selected>0)
						{
							$flag_obj_selected = 1;
						}
						else
						{
							//retorna id do atributo do tipo obj_ref que referncia o objeto selecionado em obj_fk_id
							$attr_obj_ref_id = execute_query("SELECT id
										FROM attribute
										WHERE value_type = 'obj_ref'
										AND obj_fk_id = {$obj_id}
										AND obj_id in (SELECT obj_id
												FROM attribute
												WHERE id={$attr_id})");

							$attr_obj_ref_id_row = mysqli_fetch_assoc($attr_obj_ref_id);
							
							$obj_ref_id = $attr_obj_ref_id_row["id"];
							
							
						}

						
						if($op!="vazio" && isset($op))
						{
						//guarda o operador escolhido 
						$operador = $operadores[$op];
							//caso seja do tipo text o input
							if(isset($text))
							{
								if($flag_obj_selected==1)
								{
								
									$query .= " AND obj_inst_id IN (SELECT obj_inst_id
											FROM value
										       WHERE attr_id = {$attr_id}
										       AND value {$operador} '{$text}')";
								}
								else if($flag_obj_selected==0)
								{
									$query .= " AND value.obj_inst_id = (SELECT DISTINCT value
										 FROM value , attribute
									       WHERE attr_id = $obj_ref_id
									       AND value = {$obj_inst_id_value}
										AND obj_inst_id in (SELECT obj_inst_id 
				                                                 FROM value
				                                                 WHERE attr_id = {$attr_id}
				                                                 AND value {$operador} '{$text}'))";
								}
							}
							//caso seja do tipo int
							if(isset($int))
							{
								if($flag_obj_selected==1)
								{
									$query .= " AND obj_inst_id IN (SELECT obj_inst_id
											FROM value
										       WHERE attr_id = {$attr_id}
										       AND value {$operador} {$int})";
								}
								else if($flag_obj_selected==0)
								{
									$query .= " AND value.obj_inst_id = (SELECT DISTINCT value
										 FROM value , attribute
									       WHERE attr_id = $obj_ref_id
									       AND value = {$obj_inst_id_value}
										AND obj_inst_id in (SELECT obj_inst_id 
				                                                 FROM value
				                                                 WHERE attr_id = {$attr_id}
				                                                 AND value {$operador} {$int}))";
								}
							}
							//caso seja do tipo double
							if(isset($double))
							{
								
								if($flag_obj_selected==1)
								{
									$query .= " AND obj_inst_id IN (SELECT obj_inst_id
											FROM value
										       WHERE attr_id = {$attr_id}
										       AND value {$operador} {$double})";
								}
								else if($flag_obj_selected==0)
								{
									$query .= " AND value.obj_inst_id = (SELECT DISTINCT value
										 FROM value , attribute
									       WHERE attr_id = $obj_ref_id
									       AND value = {$obj_inst_id_value}
										AND obj_inst_id in (SELECT obj_inst_id 
				                                                 FROM value
				                                                 WHERE attr_id = {$attr_id}
				                                                 AND value {$operador} {$double}))";
								}
							}
							//caso seja do tipo bool
							if(isset($bool))
							{
								
								if($flag_obj_selected==1)
								{
									$query .= " AND obj_inst_id IN (SELECT obj_inst_id
											FROM value
										       WHERE attr_id = {$attr_id}
										       AND value {$operador} {$bool})";
								}
								else if($flag_obj_selected==0)
								{
									$query .= " AND value.obj_inst_id = (SELECT DISTINCT value
										 FROM value , attribute
									       WHERE attr_id = $obj_ref_id
									       AND value = {$obj_inst_id_value}
										AND obj_inst_id in (SELECT obj_inst_id 
				                                                 FROM value
				                                                 WHERE attr_id = {$attr_id}
				                                                 AND value {$operador} {$bool}))";
								}
								
							}
							//caso seja do tipo enum
							if(isset($value))
							{
								
								if($flag_obj_selected==1)
								{
									$query .= " AND obj_inst_id IN (SELECT obj_inst_id
											FROM value
										       WHERE attr_id = {$attr_id}
										       AND value {$operador} '{$value}')";
								}
								else if($flag_obj_selected==0)
								{
									$query .= " AND value.obj_inst_id = (SELECT DISTINCT value
										 FROM value , attribute
									       WHERE attr_id = $obj_ref_id
									       AND value = {$obj_inst_id_value}
										AND obj_inst_id in (SELECT obj_inst_id 
				                                                 FROM value
				                                                 WHERE attr_id = {$attr_id}
				                                                 AND value {$operador} '{$value}'))";
								}									
							}
						}
							
					}
					//retorna o resultado da pesquisa na base de dados
					$result = execute_query($query);

					$nr_result = mysqli_num_rows($result);
					for($l=0; $l<$nr_result;$l++)
					{
						
						$result_row = mysqli_fetch_assoc($result);
						$obj_inst_id_display = $result_row["obj_inst_id"];
						
						array_push($obj_inst_id_array,$obj_inst_id_display);
					}
					$attr_id_flag=1;
				}
				
				//subtitulo mostrado no estado execução
				echo '<h3> <b> <i>  Pesquisa Dinâmica - execução merda</i> </b></h3>';
				if(count($obj_inst_id_array)!=0)
				{
					//inicia a tabela
					echo '<table style="text-align: left; width:100%" cellspacing="2" cellpadding = "2" border = "1">
						<tr>';
					//var_dump($attr_checked_id_display);
					//var_dump($obj_inst_id_array);
					//itera o array e escreve os nomes das colunas 
					for($i=0;$i<count($attr_checked_name);$i++)
					{	
												
						//primeira linha da tabela
						echo '<th> <b>'.$attr_checked_name[$i].'</b> </th>';
						
					}
					echo '</tr>';
					
					//itera o array e escreve os nomes das colunas 
					for($i=0;$i<count($obj_inst_id_array);$i++)
					{
						$obj_inst_id = $obj_inst_id_array[$i];
						echo '<tr>';
						for($k=0;$k<count($attr_checked_id_display);$k++)
						{
							$attr_checkd_id=$attr_checked_id_display[$k];
							
							//retorna um tuplo se o atributo 'atual' pertence ao objeto escolhido
							$attr_obj_selected = execute_query("SELECT id
											FROM attribute
											WHERE obj_id = {$obj_id}
											AND id = {$attr_checkd_id}");
							
							$nr_attr_obj_selected = mysqli_num_rows($attr_obj_selected);					
							
							//se for atributo do objeto selecionado
							if($nr_attr_obj_selected>0)
							{
								$flag_obj_selected = 1;
							}
							else
							{
								//retorna id do atributo do tipo obj_ref que referncia o objeto selecionado em obj_fk_id
								$attr_obj_ref_id = execute_query("SELECT id
											FROM attribute
											WHERE value_type = 'obj_ref'
											AND obj_fk_id = {$obj_id}
											AND obj_id in (SELECT obj_id
													FROM attribute
													WHERE id={$attr_checkd_id})");

								$attr_obj_ref_id_row = mysqli_fetch_assoc($attr_obj_ref_id);
								
								$obj_ref_id = $attr_obj_ref_id_row["id"];
								
								
							}

							//se for um atributo do objeto selecionado
							if($flag_obj_selected == 1)
							{
								$attr_value_query = "SELECT value
										FROM value
										WHERE attr_id={$attr_checkd_id}
										AND obj_inst_id={$obj_inst_id}";
								//retorna o valor do atributo 'atual'
								$attr_value_result = execute_query($attr_value_query);

								$attr_value_row = mysqli_fetch_assoc($attr_value_result );								

								$attr_value=$attr_value_row["value"]; 
							}
							/*else	
							{
							
							}*/
														
							echo '<td>'.$attr_value.' </td>';	
						}
						echo '</tr>';
					}
					echo '</table>';
				}
				else
				{
					echo 'Nao foi encontrado nenhum objeto com os trios selcionados';
				}
				break;

		}	
	}

}
else
{
	 printf("Não tem autorização para aceder a esta página");
}
