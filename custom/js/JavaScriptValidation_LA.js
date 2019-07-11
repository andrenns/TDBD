
//JAVA SCRIPT COM ALGUNS ERROS, NÃO FUNCIONA CORRETAMENTE, E NÃO FUNCIONANDO APENAS ALGUNS TOPICOS SURGEM ERROS NAS QUERIES

function makeServerSideValidation(DO_SERVER_SIDE_VALIDATION)
{
    if(!DO_SERVER_SIDE_VALIDATION)
    {
        var FORM=document.forms["Formulario_Gestao_Atributos"];

        var name= FORM["nome"].value;

        if(name == "")
        {
            alert("Insira um valor para o nome");
            return false;
        }
    /*
        var tipo_valor= FORM["tipo_valor"].checked;
        var valor=null;
        for(var i=0; i!=tipo_valor.length;i++)
        {
            if(tipo_valor[i]!=null)
            {
                valor=tipo_valor[i];
            }
        }

        if(valor == null)
        {
            alert("Escolha uma opção para Tipo de valor");
            return false;
        }*/

        var tipo_obj= FORM["tipo_objecto"].value;

        if(tipo_obj == null)
        {
            alert("Escolha uma opção para Tipo de valor");
            return false;
        }
        /*
        var tipo_cf= FORM["tipo_campo"].checked;
        var tipo=null;
        for(var i=0;i!=tipo_cf.length;i++)
        {
            if(tipo_cf[i]!=null)
            {
                tipo=tipo_cf[i];
            }
        }
        if(tipo == null)
        {
            alert("Escolha uma opção para Tipo de valor");
            return false;
        }*/

        var ordem_campo= FORM["ordem_campo"].value;

        if(isNaN(ordem_campo) && ordem_campo>0)
        {
            alert("Escolha uma opção para Tipo de valor");
            return false;
        }

        var tam_campo= FORM["tamanho_campo"].value;

        if(tam_campo == null && (tipo_cf == 'text' || tipo_cf == 'text box'))
        {
            alert("Escolha uma opção para Tipo de valor");
            return false;
        }

        var tam_campo= FORM["tamanho_campo"].value;

        if(tam_campo == null)
        {
            alert("Escolha uma opção para Tipo de valor");
            return false;
        }
        /*
        var obrigatorio= FORM["obrigatorio"].checked;
        var ob=null;

        for(var i=0;i!=obrigatorio.length;i++)
        {
            if(obrigatorio[i]!=null)
            {
                ob=obrigatorio[i];
            }
        }
        if(ob == null)
        {
            alert("Escolha uma opção para Tipo de valor");
            return false;
        }*/
    }
}