var materialArray = [];

function init(){
	if(localStorage.materialCOTRecord){
		materialArray = JSON.parse(localStorage.materialCOTRecord);
		for(var i=0; i<materialArray.length;i++){
			prepareTableCell(Object.keys(materialArray)[i],materialArray[i].materialCode, materialArray[i].materialName, materialArray[i].materialQty);
		}
		addValue()
	}
}

function S4() {
	return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
}

$( "#material_check_out" ).submit(function( event ) {
	localStorage.removeItem('materialCOTRecord')
});

function addItems(materialCode, materialName){
	var materialCodeJSON = { materialCode : materialCode};

	$.ajax({
		type: "POST",
		url: "/checkout/checkMaterial",
		data: materialCodeJSON,
		success: function (data) {
			console.log(data);
            var materialTable = document.getElementById("materialTable");
			var materialQty = 1;
			var materialArray = JSON.parse(localStorage.getItem("materialCOTRecord"));
			if (materialArray == null)
			{
				materialArray = [];
			}
			var alreadyExists = materialArray.filter(function(item){
				return materialName === item.materialName
			}).length;

			var fieldId = "";
			materialArray.filter(function(item, index){
				if(materialName === item.materialName){
					fieldId = index;
					return index
				}
			});
			var i = materialArray.length;
			guid = (S4() + S4() + "-" + S4() + "-4" + S4().substr(0,3) + "-" + S4() + "-" + S4() + S4() + S4()).toLowerCase();

			if (alreadyExists > 0) {
				console.log(1);
				var qtyArray = JSON.parse(localStorage.materialCOTRecord);

				for (var i = 0; i < qtyArray.length; i++) {
					qtyArray[fieldId].materialQty += 1;
					break;
				}
				localStorage.setItem("materialCOTRecord", JSON.stringify(qtyArray));
				$( "#material_check_out_hiddenForm" ).val(localStorage.materialCOTRecord);
				materialTable.rows[fieldId+1].cells[2].innerHTML = '<input type="number" style="text-align: right;" name="quantity" id="'+ fieldId +'" onchange="updateQTYField(this.id)" min="1" value="'+ qtyArray[fieldId].materialQty +'">';
			}
			else{
				var materialJSON = {id: guid, materialCode: materialCode, materialName: data['materialCode']['materialName'],materialQty: materialQty};
				materialArray.push(materialJSON);
				localStorage.materialCOTRecord = JSON.stringify(materialArray);
				$( "#material_check_out_hiddenForm" ).val(localStorage.materialCOTRecord);
				prepareTableCell(Object.keys(materialArray)[i], materialCode, data['materialCode']['materialName'], materialQty);
				i++;
			}
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert('Error : ' + errorThrown);
		}
	});
}

function updateQTYField(id) {
	var fieldId = parseInt($('#'+id).val());
	var materialItem = JSON.parse(localStorage.materialCOTRecord);
	var table = document.getElementById("materialTable");
	materialItem[id].materialQty = fieldId;
	localStorage.setItem("materialCOTRecord", JSON.stringify(materialItem));
	$( "#material_check_out_hiddenForm" ).val(localStorage.materialCOTRecord);
	table.rows[parseInt(id)+1].cells[2].innerHTML = '<input type="number" style="text-align: right;" id="'+ id +'" onchange="updateQTYField(this.id)" name="quantity" min="1" value="'+ fieldId +'">';
}

function upTo(el, tagName) {
	tagName = tagName.toLowerCase();
	while (el && el.parentNode) {
		el = el.parentNode;
		if (el.tagName && el.tagName.toLowerCase() == tagName) {
			return el;
		}
	}
	return null;
}

function deleteItem(materialCode, el){
	var materialCode = materialCode;
	var materialArray = JSON.parse(localStorage.getItem("materialCOTRecord"));

	var materialArray = materialArray.filter(function(item){
		return item.materialCode !== materialCode;
	});
	localStorage.materialCOTRecord = JSON.stringify(materialArray);
	var row = upTo(el, 'tr');
	if(row) row.parentNode.removeChild(row);
	var table = document.getElementById("materialTable");
	for(var i = table.rows.length - 1; i > 0; i--)
	{
		table.deleteRow(i);
	}
	init();
	addValue();
}

function addValue(){
	var materialArray = JSON.parse(localStorage.getItem("materialCOTRecord"));
	$( "#material_check_out_hiddenForm" ).val(JSON.stringify(materialArray));
}

function prepareTableCell(id, materialCode, materialName, materialQty){
	var materialTable = document.getElementById("materialTable");
	var row = materialTable.insertRow();
	var materialCodeCell = row.insertCell(0)
	var materialNameCell = row.insertCell(1);
	var materialQtyCell = row.insertCell(2);
	var deleteCell = row.insertCell(3);
	materialCodeCell.style.textAlign = "center";
	materialNameCell.style.textAlign = "left";
	materialQtyCell.style.textAlign = "center";
	deleteCell.style.textAlign = "center";


	materialCodeCell.innerHTML = materialCode;
	materialNameCell.innerHTML = materialName;
	materialQtyCell.innerHTML = '<input type="number" style="border-radius: 5px; text-align: right;"  id="'+ id +'" onchange="updateQTYField(this.id)" name="quantity" min="1" value="'+ materialQty +'">';
	deleteCell.innerHTML = '<a href="#" id="'+ materialCode +'" name="remove" onclick="deleteItem(this.id,this)" ><i class="fa fa-trash text-danger" aria-hidden="true"></i></a>';
}

var options = {
	url: function(phrase) {
		return "/material_warehouse/search";
	},
	getValue: function(value) {
		return value.materialCode +' - '+value.materialName+' จำนวน '+value.materialQty;
	},
	list: {
		onClickEvent: function() {
			var materialCode = $("#material_check_out_materialName").getSelectedItemData().materialCode;
			var materialName = $("#material_check_out_materialName").getSelectedItemData().materialName;
			addItems(materialCode, materialName);
			$("#material_check_out_materialName").val("");
		},
		match: {
			enabled: true
		},
		maxNumberOfElements: 10,
		showAnimation: {
			type: "fade", //normal|slide|fade
			callback: function() {}
		},
		hideAnimation: {
			type: "slide", //normal|slide|fade
			callback: function() {}
		}
	},
	ajaxSettings: {
		dataType: "json",
		method: "POST",
		data: {
			dataType: "json"
		}
	},
	preparePostData: function(data) {
		data.phrase = $("#material_check_out_materialName").val();
		return data;
	},

};

$("#material_check_out_materialName").easyAutocomplete(options);