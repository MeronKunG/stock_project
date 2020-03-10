// function setValue(){
// 	var x = sessionStorage.getItem("materialSize");
// }

function init() {
	if(localStorage.skuUpdateRecord){	
		materialArray = JSON.parse(localStorage.skuUpdateRecord);
		for(var i=0; i<materialArray.length;i++){
			prepareTableCell(Object.keys(materialArray)[i],materialArray[i].materialCode, materialArray[i].materialName, materialArray[i].materialQty);
		}
		$( "#update_sku_materialSizeForm" ).val(sessionStorage.getItem("materialSize"));
		$( "#update_sku_hiddenForm" ).val(localStorage.skuUpdateRecord);
	}
}

function S4() {
	return (((1+Math.random())*0x10000)|0).toString(16).substring(1); 
}

function generatesku(){
	$.ajax({
		url: "/sku/generatesku",
		success: function (data, dataType) {
			var skuCodeField = document.getElementById("sku_info_skuCode");
			skuCodeField.value = data;
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert('Error : ' + errorThrown);
		}
	});
}

$(".deleteSkuinfo").on("click", function(){
	var data = {request : $(this).attr('id')};
	var skuCode = $(this).attr('id');
	var bodyItem = document.getElementById('modalSkuCode');
	bodyItem.innerHTML = 'คุณต้องการลบ '+ skuCode + ' หรือไม่';
	$(".btn-yes").on("click", function(){
		$.ajax({
			type: "POST",
			url: "/sku/delete",
			data: data,
			success: function (data, dataType) {
				location.reload();
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				alert('Error : ' + errorThrown);
			}
		});
		$("#deleteModal").modal('hide');
	});
});

var materialArray = [];

function addSkuInfo(){
	var skuCode = document.getElementById("sku_info_skuCode").value;
	var skuName = document.getElementById("sku_info_skuName").value;
	
	var data = {skuCode : skuCode, skuName : skuName};
	return $.ajax({
		type: "POST",
		url: "/sku/addsku",
		data: data,
		success: function (data, dataType) {
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert('Error : ' + errorThrown);
		}
	});
}

function addItems(materialCode,meterialName){
	
	var materialName = meterialName;
	var materialCodeJSON = { materialCode : materialCode};
	var materialCode= materialCode;
	$.ajax({
		type: "POST",
		url: "/sku/checkmaterial",
		data: materialCodeJSON,
		success: function (data, dataType) {
			var materialQty = 1;
			var materialArray = JSON.parse(localStorage.getItem("skuUpdateRecord"));
			if (materialArray == null) 
			{
				materialArray = [];
			} 
			var alreadyExists = materialArray.filter(function(item){
				return materialName === item.materialName
			}).length;

			var fieldId = "";
			var alreadyMaterialName = materialArray.filter(function(item, index){
				if(materialName === item.materialName){
					fieldId = index;
					return index
				}
			});

			var i = materialArray.length;
			guid = (S4() + S4() + "-" + S4() + "-4" + S4().substr(0,3) + "-" + S4() + "-" + S4() + S4() + S4()).toLowerCase();
			materialTable = document.getElementById("materialTable");

			if (alreadyExists > 0) {
				var qtyArray = JSON.parse(localStorage.skuUpdateRecord);

				for (var i = 0; i < qtyArray.length; i++) {
					qtyArray[fieldId].materialQty += 1;
					break;
				}
				localStorage.setItem("skuUpdateRecord", JSON.stringify(qtyArray));
				$( "#update_sku_hiddenForm" ).val(localStorage.skuUpdateRecord);
				materialTable.rows[fieldId+1].cells[2].innerHTML = '<input type="number" style=" text-align: right;" name="quantity" id="'+ fieldId +'" onchange="updateQTYfield(this.id)" min="1" value="'+ qtyArray[fieldId].materialQty +'">';
			}
			else{
				var materialJSON = {id: guid, materialCode: materialCode, materialName: data['MaterialName'],materialQty: materialQty};
				materialArray.push(materialJSON);
				localStorage.skuUpdateRecord = JSON.stringify(materialArray);
				$( "#update_sku_hiddenForm" ).val(localStorage.skuUpdateRecord);
				prepareTableCell(Object.keys(materialArray)[i], materialCode, data['MaterialName'], materialQty);
				i++;
			}
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert('Error : ' + errorThrown);
		}
	});
}

function updateQTYfield(id) {
	var fieldId = parseInt($('#'+id).val());
	var skuItem = JSON.parse(localStorage.skuUpdateRecord);
	var table = document.getElementById("materialTable");
	skuItem[parseInt(id)].materialQty = fieldId;
	localStorage.setItem("skuUpdateRecord", JSON.stringify(skuItem));
	$( "#update_sku_hiddenForm" ).val(localStorage.skuUpdateRecord);
	table.rows[parseInt(id)+1].cells[2].innerHTML = '<input type="number" style=" text-align: right;" id="'+ id +'" onchange="updateQTYfield(this.id)" name="quantity" min="1" value="'+ fieldId +'">';
	// location.reload();
}

function deleteItem(materialName, el){
	var materialName = materialName;
	var materialArray = JSON.parse(localStorage.getItem("skuUpdateRecord"));

	var materialArray = materialArray.filter(function(item){
		return item.materialName !== materialName;
	});
	localStorage.skuUpdateRecord = JSON.stringify(materialArray);
	var row = upTo(el, 'tr')
	if(row) row.parentNode.removeChild(row);
	var table = document.getElementById("materialTable");
	for(var i = table.rows.length - 1; i > 0; i--)
	{
		table.deleteRow(i);
	}
	init();
	$( "#update_sku_hiddenForm" ).val(localStorage.skuUpdateRecord);
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
	materialQtyCell.innerHTML = '<input type="number" style="border-radius: 5px;text-align: right;"   id="'+ id +'" onchange="updateQTYfield(this.id)" name="quantity" min="1" value="'+ materialQty +'">';
	// deleteCell.innerHTML = '<button id="'+ materialName +'" name="remove" class="btn btn-danger btn-del" onclick="deleteItem(this.id,this)">Delete</button>';
	deleteCell.innerHTML = '<a href="#" id="'+ materialName +'" name="remove" onclick="deleteItem(this.id,this)" ><i class="fa fa-trash text-danger" aria-hidden="true"></i></a>';
}

$("#update").click(function(){
	var skuStatus = $("#update_sku_skuStatus option:selected").val();
	var skuCode = $( "#update_sku_skuCode" ).val();
	var skuName = $( "#update_sku_skuName" ).val();
	if (typeof skuStatus != "undefined" || skuStatus != null){
		updateSKUStatus(skuCode,skuStatus);
	}

	var materialArray = JSON.parse(localStorage.getItem("skuUpdateRecord"));
	if(materialArray.length <= 0)
	{
		alert("กรุณาเลือก Material ให้ถูกต้อง");
	}
	else{
		if (materialArray == null) 
		{
			materialArray = [];
		}
		deleteAllSkuItem(skuCode).done(function(data){
			for(var i=0; i<materialArray.length;i++){
				var data = {
					skuCode : skuCode,
					materialCode : materialArray[i].materialCode, 
					materialName : materialArray[i].materialName, 
					materialQty: materialArray[i].materialQty
				};
				$.ajax({
					type: "POST",
					url: "/sku/updateBom",
					data: data,
					success: function (data, dataType) {
					// localStorage.clear();
					if(data != null){
						window.location.href = '/sku/info';
					}
					else{
						alert
						window.location.href = '/sku/info';
					}
					// location.reload();
					
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					alert('Error : ' + errorThrown);
				}
			});
			}
		});
	}
});

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function deleteAllSkuItem(skuCode){
	var fieldSize = parseInt(parseInt(sessionStorage.getItem("materialSize")));
	data = { skuCode: skuCode };
	if(fieldSize > 0){
		for(var i=0; i<fieldSize;i++){
			return $.ajax({
				type: "POST",
				dataType: "json",
				url: "/sku/deleteAllSkuItem",
				data: data,
				success: function (data, dataType) {
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					alert('Error : ' + errorThrown);
				}
			});
		}
	}
	else{
		return $.ajax({
			data:data,
			success: function (data) {
			}
		});
	}
}

function updateSKUStatus(skuCode,skuStatus){
	data = { skuCode: skuCode,skuStatus : skuStatus };
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "/sku/updateStatus",
		data: data,
		success: function (data, dataType) {
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert('Error : ' + errorThrown);
		}
	});
}

function updateSKUStatus(skuCode,skuStatus){
	data = { skuCode: skuCode,skuStatus : skuStatus };
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "/sku/updateStatus",
		data: data,
		success: function (data, dataType) {
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert('Error : ' + errorThrown);
		}
	});
}

var options = {
	url: function(phrase) {
		return "/material/search";
	},
	getValue: function(element) {
		return element.materialCode +' - '+element.materialName;
	},
	list: {
		onClickEvent: function() {
			var meterialCode = $("#materialNameForm").getSelectedItemData().materialCode;
			var meterialName = $("#materialNameForm").getSelectedItemData().materialName;
			addItems(meterialCode,meterialName);
			$("#materialNameForm").val("");
		},
		match: {
			enabled: true
		},
		maxNumberOfElements: 10,
		showAnimation: {
			type: "fade", //normal|slide|fade
			// time: 400,
			callback: function() {}
		},
		hideAnimation: {
			type: "slide", //normal|slide|fade
			// time: 400,
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
		data.phrase = $("#materialNameForm").val();
		return data;
	},

};

$("#materialNameForm").easyAutocomplete(options);
