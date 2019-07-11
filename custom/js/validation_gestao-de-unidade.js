function validateForm() {
    var x = document.forms["form1"]["text_name"].value;
    if (x == "") {
        alert("O campo da unidade não pode estar vazio.");
        return false;
    }
}

