var sel = document.getElementById('select');
sel.onchange = function(){
    document.body.className = sel.value;
};

var saveclass = null;
var sel = document.getElementById('select');
sel.onchange = function(){
    saveclass = saveclass ? saveclass : document.body.className;
    document.body.className = saveclass + ' ' + sel.value;
};