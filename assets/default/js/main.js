(function(){
	var draganddrop = get('dragAndDrop');
	var upicon = get('upicon');
	var spin = get('spin');
	var progress = get('progress');
	var alerts = get('alert');
	var modalUpload = get('modalUpload');
	var modalOverlay = get('modal');
	
	var upload = function (files) {
		var xhr = new XMLHttpRequest();
		var formData = new FormData();
		
		for (var i in files) {
		    formData.append("files[]", files[i]);	
		}
		
		xhr.upload.onprogress = function (event) {
		    var percent = ( event.loaded / event.total ) * 100;	
		    var text = get('hide');
			percent = Math.round(percent);
					
			upicon.style.display = "none";
			text.style.display = "none";
		
			spin.style.display = "block";
	
			progress.style.display = "block";
			progress.innerHTML = percent + "%";
			
		};
		
		xhr.onreadystatechange = function () {
		   if (xhr.readyState === 4) {
		   	    var complete = get('complete');
		        var data = JSON.parse(xhr.responseText);
		        console.log(data);
				if (data.error) {
					alerts.style.display = "block";
					alerts.className = "alert alert-danger";
					alerts.innerHTML = data.error;
				}
				
				if (data.success) {
					window.location = data.url;
				}
				
				complete.style.display = "block";
				spin.style.display = "none";
				progress.style.display = "none";
		   }	
		};
		
		xhr.open("POST", "/upload");
		xhr.send(formData);
	};
	
	draganddrop.ondrop =  function (event) {
		event.preventDefault();
		
		draganddrop.className = "draganddrop";
		
		upload(event.dataTransfer.files);	
	};
	
	draganddrop.ondragover = function () {
		draganddrop.className = "draganddrop dragover";
		
		return false;	
	};
	
	draganddrop.ondragleave = function() {
		draganddrop.className = "draganddrop";
		
		return false;	
	};
	
	draganddrop.onclick = function () {
		var fileInput = get('file');
		fileInput.click(function (event) { event.preventDefault(); });
		
		fileInput.onchange = function (event) {
			upload(fileInput.files);	
		};	
	};
}())