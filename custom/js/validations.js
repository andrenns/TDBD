function performCSValidationGO(DO_SERVER_SIDE_VALIDATION)
{
	
	if(!DO_SERVER_SIDE_VALIDATION)
		{
			var form = document.forms["gestao-de-objetos_introducao"];
			
			var name = form["obj_name"].value;

			if(name=="")
				{
					alert("Necessita inserir valor para o nome");
					return false;
				}

			var type = form["obj_type"].value;

			if(type=="")
				{
					alert("Selecione um tipo");
					return false;
				}
			
			var state = form["obj_state"].value;

			if(state=="")
				{
					alert("Selecione um estado");
					return false;
				}

		}
}

function performCSValidationGVP(DO_SERVER_SIDE_VALIDATION)
{
	if(!DO_SERVER_SIDE_VALIDATION)
		{
			var allowed_value = document.forms["gestao-de-valores-permitidos_introducao"]["allowed_value"].value;

			if(allowed_value =="")
				{
					alert("Necessita inserir nome para o valor permitido");
					return false;
				}
		}
}
