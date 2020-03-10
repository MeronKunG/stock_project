///////////////////////////////WAREHOUSE///////////////////////////////////////////////////////////////////////////

$(".deleteWarehouse").on("click", function(){
	var data = {request : $(this).attr('id')};
	$(".btn-yes").on("click", function(){
		$.ajax({
			type: "POST",
			url: "/delete/warehouse",
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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////MATERIALINFO//////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// $(document).ready(function(){
// 	$('[data-toggle="tooltip"]').tooltip();
// });

// $("#material_info_materialName").keyup(function (e) {
// 	var materialNameVal = $("#material_info_materialName").val().length;
// 	$("#materialNameVal").html(materialNameVal+"/50");
// });
// $("#material_info_materialFullName").keyup(function (e) {
// 	var materialFullNameVal = $("#material_info_materialFullName").val().length;
// 	$("#materialFullNameVal").html(materialFullNameVal+"/255");
// });
//
// $("#material_info_materialName").change(function (e) {
// 	var materialName = $(this).val();
// 	var materialNameVal = $(this).val().length;
// 	$.post('/material/checkmaterialName', {'materialName':materialName}, function(data) {
// 		if(data === true){
// 			$("#material_info_materialName").removeClass( "is-valid" ).addClass( "is-invalid" );
// 			$("#materialName-result").html( "มีชื่อ " +materialName+" อยู่แล้ว" );
// 			$("#materialName-result").removeClass( "valid-feedback" ).addClass( "invalid-feedback" );
// 		} else{
// 			$("#material_info_materialName").removeClass( "is-invalid" ).addClass( "is-valid" );
// 			$("#materialName-result").html("ชื่อ " +materialName+" นี้สามารถใช้ได้");
// 			$("#materialName-result").removeClass( "invalid-feedback" ).addClass( "valid-feedback" );
//
// 		}
// 	});
// });

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////SKUINFO///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// function S4() {
// 	return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
// }
//
// $(".viewSkuDetail").click(function(){
// 	var skuCode = { skuCode : $(this).attr('id') };
// 	var skuCodeStr = $(this).attr('id');
// 	var bodyItem = document.getElementById('modal-body');
// 	var tableItem = document.getElementById('skuBodyData');
// 	var titlecode = document.getElementById('titlecode');
// 	tableItem.innerHTML ="";
// 	$.ajax({
// 		type: "POST",
// 		url: "/sku/view/",
// 		data: skuCode,
// 		success: function (data, dataType) {
// 			titlecode.innerHTML = 'SKU Code : '+ skuCodeStr;
// 			if (data.skuCode != null) {
// 				for (let idx in data.bom) {
// 					tableItem.innerHTML += '<tr><td style="text-align: left;">' + data.bom[idx].material.materialName + '</td><td style="text-align: center;">' + data.bom[idx].quantity + '</td></tr>';
// 				}
// 			} else {
// 				tableItem.innerHTML += '<tr><td>ไม่มีข้อมูล</td><td></td></tr>';
// 			}
// 		},
// 		error: function (XMLHttpRequest, textStatus, errorThrown) {
// 			alert('Error : ' + errorThrown);
// 		}
// 	});
// });
//
// $(".updateSkuDetail").click(function(){
// 	var skuCode = { skuCode : $(this).attr('id') };
// 	materialArray = [];
// 	localStorage.skuUpdateRecord = JSON.stringify(materialArray);
// 	sessionStorage.removeItem("materialSize");
// 	guid = (S4() + S4() + "-" + S4() + "-4" + S4().substr(0,3) + "-" + S4() + "-" + S4() + S4() + S4()).toLowerCase();
// 	$.ajax({
// 		type: "POST",
// 		url: "/sku/view/",
// 		data: skuCode,
// 		success: function (data, dataType) {
// 			if(data.skuCode !=null){
// 				sessionStorage.setItem("materialSize", data.bom.length);
// 				for(let idx in data.bom)
// 				{
// 					var materialJSON = {id: guid, materialCode: data.bom[idx].material.materialCode, materialName: data.bom[idx].material.materialName, materialQty: data.bom[idx].quantity};
// 					materialArray.push({id: guid, materialCode: data.bom[idx].material.materialCode, materialName: data.bom[idx].material.materialName, materialQty: data.bom[idx].quantity});
// 				}
// 				localStorage.skuUpdateRecord = JSON.stringify(materialArray);
// 			}
// 			else{
// 				sessionStorage.setItem("materialSize", 0);
// 			}
// 		},
// 		error: function (XMLHttpRequest, textStatus, errorThrown) {
// 			alert('Error : ' + errorThrown);
// 		}
// 	});
// });

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////CHECKININFO///////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// $(".viewCheckInDetail").click(function(){
// 	var checkInCode = { checkInCode : $(this).attr('id') };
// 	var checkInCodeStr = $(this).attr('id');
// 	var bodyItem = document.getElementById('modal-body1');
// 	var tableItem = document.getElementById('checkInTableItem');
// 	var titleCode = document.getElementById('checkInTitleCode');
// 	tableItem.innerHTML ="";
// 	// alert(checkInCodeStr);
// 	$.ajax({
// 		type: "POST",
// 		url: "/checkin/view/",
// 		data: checkInCode,
// 		success: function (data, dataType) {
// 			titleCode.innerHTML = 'Check In Code : '+ checkInCodeStr;
// 			if(data.materialCode !=null){
// 				for(var i=0; i<data.checkInCode.length; i++)
// 				{
// 					tableItem.innerHTML += '<tr><td style=" text-align: center;">'+data.checkInCode[i]+'</td><td style="text-align: left;">'+data.materialName[i]+'</td><td style="text-align: center;">'+data.materialQty[i]+'</td></tr>';
// 				}
// 			} else {
// 				tableItem.innerHTML += '<tr><td>ไม่มีข้อมูล</td><td></td><td></td></tr>';
// 			}
// 		},
// 		error: function (XMLHttpRequest, textStatus, errorThrown) {
// 			alert('Error : ' + errorThrown);
// 		}
// 	});
// });

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////TRANSECTIONINFO///////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var options = {
	url: function(phrase) {
		return "/material/transaction/selectmaterial";
	},
	getValue: function(element) {
		return element.materialCode;//+'-'+element.materialName;
	},
	list: {
		onClickEvent: function() {
			$("#transactionForm").change(searchTransaction).change();
			searchTransaction.bind($('#transactionForm'));
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
		data.phrase = $("#transactionForm").val();
		return data;
	},

};
$("#transactionForm").easyAutocomplete(options);

var searchRequest = null;

function searchTransaction() {
	var value = '';
	value = $(this).val();
	alert(value);
	var sumQTY=0;
	var sumQTYt = [];
	var tableItem = document.getElementById('transactionTableItem');

	$.ajax({
		type: "GET",
		url: "/material/transaction/search",
		data: {'q' : value},
		success: function (data, dataType) {
			materialTrans = data.materialTrans;


		for(var i=0; i < materialTrans.materialTransactionCode.length;i++)
		{
			console.log(JSON.stringify(materialTrans.materialTransactionCode[i]+' '+materialTrans.materialTransactionMaterialName[i]));

		// 	tableItem.innerHTML += '<tr><td>'+data.materialTransCode[i]+'</td><td>'
		// 							+data.materialTransDate[i].date+'</td><td>'
		// 							+data.materialTransMaterialName[i]+'</td></tr>'
		// 							+data.materialTransQuantity[i]+'</td></tr>';
	}
},
error: function (XMLHttpRequest, textStatus, errorThrown) {
	alert('Error : ' + errorThrown);
}
});
}
