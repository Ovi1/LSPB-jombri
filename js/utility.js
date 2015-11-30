JoomBri = window.JoomBri || {};

function checkAvailable(el){
	var inputstr = el.value;
	var name = el.name;
	if(inputstr.length > 0){
		var myRequest = jQuery.ajax({
			url: "index.php?option=com_jblance&task=guest.checkuser&"+JoomBriToken,
			method: "POST",
			data: {"inputstr":inputstr, "name":name},
			beforeSend : function(){ jQuery("#status_"+name).empty().removeProp("class").addClass("jbloading dis-inl-blk"); },
			success: function(response) {
				if(response == "OK"){
					jQuery("#status_"+name).removeClass("jbloading failurebg").addClass("successbg");
					jQuery("#status_"+name).html(Joomla.JText._("COM_JBLANCE_AVAILABLE"));
				} 
				else {
					jQuery("#status_"+name).removeClass("jbloading successbg").addClass("failurebg");
					jQuery("#status_"+name).html(response);
				}
           }
		});
	}
}

jQuery(document).ready(function($){
	JoomBri.uploadCropPicture = function(task, cropped_logo, original_logo){
		$currentAvatar = $(".current-profile-picture").cropit();
		$currentAvatar.cropit("imageSrc", cropped_logo);
		$currentAvatar.cropit("previewSize", { width: 160, height: 160 });

		$(".cropit-image-view").cropit({
	        imageBackground: false,
	        imageBackgroundBorderWidth: 50,
	        smallImage: "allow",
	        imageState: {
	          src: original_logo
	        },
	        onFileChange: function(){
				$("#upload-message").empty();
				$("#upload_type").val("UPLOAD_CROP");
	        }
		});

		$(".select-image-btn").click(function() {
			$(".cropit-image-input").click();
		});

		$(".remove-picture").on("click", function(){
			removePicture($(".remove-picture").data("user-id"), $(".remove-picture").data("remove-task"));
		});
		
		$(".crop-save").click(function(){
			var imageData = $(".cropit-image-view").cropit("export", {
				type: "image/jpeg",
				quality: .75,
	        });

		    var fileData = new FormData();     
			fileData.append("profile_file", $("input[name='profile_file']")[0].files[0]);
			fileData.append("imageData", imageData);
			fileData.append("user_id", $("#user_id").val());
			fileData.append("upload_type", $("#upload_type").val());

	        var myRequest = $.ajax({
				url: "index.php?option=com_jblance&task="+task+"&"+JoomBriToken,
	    		type: "POST",
				data: fileData,
				cache: false,
				contentType: false,
				processData: false,
	    		success: function(response){ console.log(response);
	    			 $("#upload-message").empty();
	    			 var resp = jQuery.parseJSON(response);
	    			 if(resp["result"] == "OK"){
	    				 var alert = ['<div class="alert alert-info">',
	    	   	        	              resp["msg"],
	    	   	        	            '</div>'
	    	   	        	          ].join('');
						$("#upload-message").append(alert);

						$currentAvatar.cropit("imageSrc", imageData);
					}
					else if(resp["result"] == "NO"){
		   	        	var alert = ['<div class="alert alert-error">',
		   	        	              resp["msg"],
		   	        	            '</div>'
		   	        	          ].join('');
	   	        	 	$("#upload-message").append(alert);
	   	          }
	    		}
	    	});
	        return false;
	      });
	};
});


function createUploadButton(userid, task){
	var uploader = document.getElementById("photoupload");
	upclick({
		element: uploader,
		action: "index.php?option=com_jblance&task="+task+"&"+JoomBriToken,
		dataname: "photo", 
		action_params: {"userid": userid},
		onstart: 
			function(filename){ jQuery("#ajax-container").empty().removeProp("class").addClass("jbloading"); },
		oncomplete: 
			function(response){
				var resp = jQuery.parseJSON(response);
				if(resp["result"] == "OK"){
					//set the picture
					target = jQuery("#divpicture");
					target.html("<img src="+resp["image"]+" class='img-polaroid'>");
					//set the thumb
					target = jQuery("#divthumb");
					target.html("<img src="+resp["thumb"]+" class='img-polaroid'>");
					//set the crop image
					if(jQuery("#cropframe").length){
						jQuery("#cropframe").css("background-image", "url("+resp["image"]+")");
						jQuery("#imglayer").css({
							"background-image": "url("+resp["image"]+")",
						    "width": resp["width"],
						    "height": resp["height"]
						});
						jQuery("#imgname").val(resp["imgname"]);
						jQuery("#tmbname").val(resp["tmbname"]);
					}
					jQuery("#ajax-container").removeProp("class").addClass("successbg");
					jQuery("#ajax-container").html(resp["msg"]);
				}
				else if(resp["result"] == "NO"){
					jQuery("#ajax-container").removeProp("class").addClass("failurebg");
					jQuery("#ajax-container").html(resp["msg"]);
				}
			}
	});
}

function removePicture(userid, task){
	var myRequest = jQuery.ajax({
		url: "index.php?option=com_jblance&task="+task+"&"+JoomBriToken,
		method: "POST",
		data: {"userid": userid },
		//beforeSend: function(){  jQuery("#ajax-container").empty().removeProp("class").addClass("jbloading"); },
		success: function(response){
			 jQuery("#upload-message").empty();
			 var resp = jQuery.parseJSON(response);
			 if(resp["result"] == "OK"){
				 var alert = ['<div class="alert alert-info">',
	   	        	              resp["msg"],
	   	        	            '</div>'
	   	        	          ].join('');
				jQuery("#upload-message").append(alert);
	          	 
			}
			else if(resp["result"] == "NO"){
   	        	var alert = ['<div class="alert alert-error">',
   	        	              resp["msg"],
   	        	            '</div>'
   	        	          ].join('');
	        	 	jQuery("#upload-message").append(alert);
	          }
		}
	});
}

function updateThumbnail(task){
	jQuery("#editthumb").css("display", "");
	
	var ch = new CwCrop({
   	    minsize: {x: 64, y: 64},
   	    maxratio: {x: 2, y: 1},
   	    fixedratio: false,
   	 	onCrop: function(values){
   			var myRequest = jQuery.ajax({
	   			url: "index.php?option=com_jblance&task="+task+"&"+JoomBriToken,
	   			method: "POST",
	   			data: {"cropW": values.w, "cropH": values.h, "cropX": values.x, "cropY": values.y, "imgLoc": jQuery("#imgname").val(), "tmbLoc": jQuery("#tmbname").val()},
	   			beforeSend: function(){  jQuery("#tmb-container").empty().removeProp("class").addClass("jbloading"); },
	   			success: function(response){
	   				var resp = jQuery.parseJSON(response);
	   			    
					if(resp["result"] == "OK"){
						jQuery("#tmb-container").removeClass("jbloading").addClass("successbg");
						jQuery("#tmb-container").html(resp["msg"]);
	   		         }
	   		         else if(resp["result"] == "NO"){
	   		        	jQuery("#tmb-container").removeClass("jbloading").addClass("failurebg");
	   		        	jQuery("#tmb-container").html(resp["msg"]);
	   		         }
	   			}
   			});
   	   }
   	});
}

function attachFile(elementID, task){
	var uploader = document.getElementById(elementID);
	
	upclick({
		element: uploader,
		action: "index.php?option=com_jblance&task="+task+"&"+JoomBriToken,
		dataname: elementID, 
		action_params: {"elementID": elementID},
		onstart: function(filename){ jQuery("#ajax-container-"+elementID).empty().removeProp("class").addClass("jbloading"); },
		oncomplete:
			function(response){
				var resp = jQuery.parseJSON(response);
				var elementID = resp["elementID"];
				if(resp["result"] == "OK"){
					jQuery("#"+elementID).css("display", "none");
					jQuery("#ajax-container-"+elementID).removeClass("jbloading").addClass("successbg");
					jQuery("#ajax-container-"+elementID).html(resp["msg"]);
					var html = "<input type='checkbox' name='chk-"+elementID+"' checked value='1' />"+resp["attachname"]+"<input type='hidden' name='attached-file-"+elementID+"' value='"+resp["attachvalue"]+"'>";
					jQuery("#file-attached-"+elementID).html(html);
				}
				else if(resp["result"] == "NO"){
					jQuery("#ajax-container-"+elementID).removeClass("jbloading").addClass("failurebg");
					jQuery("#ajax-container-"+elementID).html(resp["msg"]);
				}
			}
	});
}

var checkUsername = function(el){
	var inputstr = jQuery(el).val();
	var name = jQuery(el).attr("name");
	//if(inputstr.length > 0){
		var myRequest = jQuery.ajax({
			url: "index.php?option=com_jblance&task=membership.checkuser&"+JoomBriToken,
            method: "POST",
			data: {"inputstr":inputstr, "name":name},
			beforeSend: function(){ jQuery("#status_"+name).empty().removeProp("class").addClass("jbloading dis-inl-blk"); },
			success: function(response) {
				var resp = jQuery.parseJSON(response);
				if(resp["result"] == "OK"){
					jQuery("#status_"+name).removeClass("jbloading").addClass("successbg");
					jQuery("#status_"+name).html(resp["msg"]);
				} 
				else {
					jQuery("#status_"+name).removeClass("jbloading").addClass("failurebg");
					jQuery("#status_"+name).html(resp["msg"]);
				}
           }
		});
	//}
};

function fillProjectInfo(){
	var project_id = jQuery("#project_id").val();
	var myRequest = jQuery.ajax({
		url: "index.php?option=com_jblance&task=membership.fillprojectinfo&"+JoomBriToken,
		method: "POST",
		data: {"project_id": project_id },
		success: function(response){
			var resp = jQuery.parseJSON(response);
			if(resp["result"] == "OK"){
				jQuery("#recipient").val(resp["assignedto"]);
          	  	//if full payment is checked, set amount to bid amount. if payment is partial, set amount to balance amount
				if(jQuery("#full_payment_option:checked").val()){
					jQuery("#amount").val(resp["bidamount"]);
				}
	          	else if(jQuery("#partial_payment_option:checked").val()){
	          		jQuery("#amount").val(resp["proj_balance"]);
	          	}
	          	jQuery("#proj_balance").val(resp["proj_balance"]);
	          	// display pay for field only for hourly projects
	          	if(resp["project_type"] == "COM_JBLANCE_HOURLY"){
	          		jQuery("#div_pay_for").css("display", "block");
	          		jQuery("#amount").val("");
	          		jQuery("#bid_amount").val(resp["bidamount"]);
          		
	          		jQuery("#pay_for").addClass("required").prop("required", "required");
	          	}
	          	else if(resp["project_type"] == "COM_JBLANCE_FIXED"){
	          		jQuery("#div_pay_for").css("display", "none");
	          		jQuery("#pay_for").removeClass("required").removeProp("required");
	          	}
			}
			else if(resp["result"] == "NO"){
				//nothing
			}
		}
	});
}

function processFeed(userid, activityid, type){
	var myRequest = jQuery.ajax({
		url: "index.php?option=com_jblance&task=user.processfeed&"+JoomBriToken,
		method: "POST",
		data: {"userid":userid, "activityid":activityid, "type":type}, 
		beforeSend: function(){ jQuery("#feed_hide_"+activityid).empty().addClass("jbloading"); },
		success: function(response){
			if(response == "OK"){
				jQuery("#jbl_feed_item_"+activityid).css("display", "none");
			}
			else {
				alert(":(");
			}
		}
	});
}

function processForum(forumid, task){
	var myRequest = jQuery.ajax({
		url: "index.php?option=com_jblance&task="+task+"&"+JoomBriToken,
		method: "POST",
		data: {"forumid":forumid}, 
		success: function(response){
			if(response == "OK"){
				jQuery("#tr_forum_"+forumid).remove();
			}
			else {
				alert(":(");	
			}
		}
	});
}

function processMessage(msgid, task){
	var myRequest = jQuery.ajax({
		url: "index.php?option=com_jblance&task="+task+"&"+JoomBriToken,
		method: "POST",
		data: {"msgid":msgid}, 
		beforeSend: function(){ jQuery("#feed_hide_"+msgid).empty().addClass("jbloading"); },
		success: function(response){
			if(response == "OK"){
				jQuery("#jbl_feed_item_"+msgid).css("display", "none");
			} 
			else
				alert(":(");
		}
	});
}

function manageMessage(msgid, type){
	var text = "";
	var removeAttach = "";
	if(type == "message"){
		text = jQuery("#message_"+msgid).val();
	}
	else if (type == "subject"){
		text = jQuery("#txt_subject_"+msgid).val();
	}
	
	if(jQuery("#chk_attachment_"+msgid).length && jQuery("#chk_attachment_"+msgid+":checked").length){
		removeAttach = jQuery("#chk_attachment_"+msgid).val();
	}
		
	var myRequest = jQuery.ajax({
		url: "index.php?option=com_jblance&task=admproject.manageMessage&"+JoomBriToken,
        method: "POST",
		data: {"msgid":msgid, "text":text , "type": type, "attachment" : removeAttach},
		success: function(response){
			var resp = jQuery.parseJSON(response);
			if(resp["result"] == "OK"){
				if(type == "message"){
					jQuery("#span_message_"+msgid).css("display","inline");
					jQuery("#span_message_"+msgid).html(jQuery("#message_"+msgid).val());
					jQuery("#message_"+msgid).css("display", "none");
					jQuery("#btn_save_message_"+msgid).css("display", "none");
					jQuery("#btn_edit_message_"+msgid).css("display", "inline");
					
					if(resp["attachRemoved"] == 1){
						jQuery("#div_attach_"+msgid).css("display", "none");
					}
				}
				else if(type == "subject"){
					jQuery("#span_subject_"+msgid).html(jQuery("#txt_subject_"+msgid).val());
					jQuery("#txt_subject_"+msgid).css("display", "none");
					jQuery("#btn_save_subject_"+msgid).css("display", "none");
				}
			} 
			else {
				alert(":(");
			}
       }
	});
}

function approveMessage(msgid, task){
	var myRequest = jQuery.ajax({
		url: "index.php?option=com_jblance&task=admproject.approvemessage&"+JoomBriToken,
		method: "POST",
		data: {"msgid":msgid}, 
		success: function(response){
			if(response == "OK"){
				jQuery("#feed_hide_approve_"+msgid).css("display", "none");
			} 
			else {
				alert(":(");
			}
		}
	});
}

function removeTransaction(transid){
	var myRequest = jQuery.ajax({
		url: "index.php?option=com_jblance&task=admproject.removetransaction&"+JoomBriToken,
		method: "POST",
		data: {"transid":transid}, 
		success: function(response){
			if(response == "OK"){
				jQuery("#tr_trans_"+transid).remove();
			}
			else {
				alert(":(");
			}
		}
	});
}

function processBid(bidid){
	var myRequest = jQuery.ajax({
		url: "index.php?option=com_jblance&task=admproject.processbid&"+JoomBriToken,
		method: "POST",
		data: {"bidid":bidid}, 
		success: function(response){
			if(response == "OK"){
				jQuery("#tr_r1_bid_"+bidid).css("display", "none");
				jQuery("#tr_r2_bid_"+bidid).css("display", "none");
			} 
			else
				alert(":(");
		}
	});
}

function favourite(targetId, action, type){
	var requestURL = "";
	if(type == "profile")
		requestURL = "index.php?option=com_jblance&task=user.favourite&"+JoomBriToken;
	
	var myRequest = jQuery.ajax({
		url: requestURL,
		method: "POST",
		data: {"targetId":targetId, "action":action}, 
		success: function(response){
			var resp = jQuery.parseJSON(response);
			if(resp["result"] == "OK"){
				jQuery("#fav-msg-"+targetId).html("<small>"+resp["msg"]+"</small>");
			} 
			else
				alert(":(");
		}
	});
}

function createDropzone(elementId, mockFile, controller){
	//Get the template HTML and remove it from the document template HTML and remove it from the doument
	var previewNode = document.querySelector("#template");
	previewNode.id = "";
	var previewTemplate = previewNode.parentNode.innerHTML;
	previewNode.parentNode.removeChild(previewNode);
	 
	var myDropzone = new Dropzone(elementId, { // Make the whole body a dropzone
	  url: "index.php?option=com_jblance&task="+controller+".serviceuploadfile&"+JoomBriToken, // Set the url
	  paramName: "serviceFile",
	  //autoDiscover : false,
	  //myAwesomeDropzone : false,
	  thumbnailWidth: 80,
	  thumbnailHeight: 80,
	  parallelUploads: 1,
	  previewTemplate: previewTemplate,
	  autoQueue: false, // Make sure the files aren't queued until manually added
	  previewsContainer: "#previews", // Define the container to display the previews
	  clickable: ".fileinput-button", // Define the element that should be used as click trigger to select files.
	  maxFilesize: 2, // MB
	  maxFiles: 5,	//limited to 5 files only
	  acceptedFiles: "image/*",
	  init: function(){
		  // if images are uploaded successfully, add hidden fields. This function is called by emit function upon load and after file is successfully uploaded 
		  this.on("success", function(file, response){
			  if(typeof response !== "undefined" && response !== null){
				  var resp = jQuery.parseJSON(response);
				  if(resp["result"] == "OK"){
					  var hiddenvalue = resp["attachvalue"];
				  }
			  }
			  else {
				  var hiddenvalue = file.name + ";" + file.servername + ";" + file.size;
			  }
			  jQuery("<input/>", {
				  "type": "hidden",
				  "name": "serviceFiles[]",
				  "value" : hiddenvalue
			  }).appendTo(file.previewTemplate);
		  }); 
	  }
	});

	var existingFileCount = 0;
	var data = jQuery.parseJSON(mockFile);
	jQuery.each(data, function(key, value){
		var mockFile = { name: value.name, servername: value.servername, size: value.size, status: Dropzone.SUCCESS };
		myDropzone.emit("addedfile", mockFile);
		myDropzone.emit("thumbnail", mockFile, value.thumb);
		myDropzone.emit("success", mockFile);
		myDropzone.files.push(mockFile);  // added this line so the files array is the correct length.
		existingFileCount = existingFileCount + 1;
	});
	
	myDropzone.options.maxFiles = myDropzone.options.maxFiles - existingFileCount;
	
	myDropzone.on("addedfile", function(file) {
		// Hookup the start button
		file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file); };

		// Add default option box for each preview.
		//var defaultRadioButton = Dropzone.createElement('<div class="default_pic_container"><input type="radio" name="default_pic" value="'+file.name+'" /> Default</div>');
		//file.previewElement.appendChild(defaultRadioButton);
	});
	 
	// Update the total progress bar
	myDropzone.on("totaluploadprogress", function(progress) {
		document.querySelector("#total-progress .bar").style.width = progress + "%";
	});

	myDropzone.on("sending", function(file) {
		// Show the total progress bar when upload starts
		document.querySelector("#total-progress").style.opacity = "1";
		// And disable the start button
		file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
	});
	 
	// Hide the total progress bar when nothing's uploading anymore
	myDropzone.on("queuecomplete", function(progress) {
		document.querySelector("#total-progress").style.opacity = "0";
	});

	myDropzone.on("removedfile", function(file){
		//if the input hidden node exist
		if(file.previewTemplate.children[4]){
			attachvalue = file.previewTemplate.children[4].value;
			
			var myRequest = jQuery.ajax({
				url: "index.php?option=com_jblance&task="+controller+".removeServiceFile&"+JoomBriToken,
				method: "POST",
				data: {"attachvalue": attachvalue },
				success: function(response){
					//nothing
				}
			});
			myDropzone.options.maxFiles = myDropzone.options.maxFiles + 1;
		}
	});
	 
	// Setup the buttons for all transfers
	// The "add files" button doesn't need to be setup because the config `clickable` has already been specified.
	document.querySelector("#actions .start").onclick = function() {
		myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
	};
	document.querySelector("#actions .cancel").onclick = function() {
		myDropzone.removeAllFiles(true);
	};
}

function getLocation(el, task){
 	var location_id = jQuery(el).val();
 	var curLevel = jQuery(el).data("level-id");
 	var nxtLevel = curLevel + 1;
 	
 	jQuery("#id_location").val(location_id);	//set the current location id for saving
 	
 	jQuery("#level"+curLevel).nextAll("select").remove();	//remove the children levels when parent level is changed
 	
	var myRequest = jQuery.ajax({
		url: "index.php?option=com_jblance&task="+task+"&"+JoomBriToken,
		method: "POST",
		data: {"location_id":location_id, "cur_level": curLevel, "nxt_level": nxtLevel, "task_val": task}, 
		beforeSend: function(){ jQuery("#ajax-container").addClass("jbloading") },
		success: function(response){
 			if(response != 0){
 				jQuery(response).insertAfter("#level"+curLevel);
			}
 			jQuery("#ajax-container").removeClass("jbloading");
		}
	});
}