(function() {
	//Open Modal Box
	document.body.addEventListener("click", function(event) {
		var target = event.target; //Set Event Target
		var atribute = target.getAttribute('data-target');// Get Data-targer (modal id)
		var toggle = target.getAttribute('data-toggle');// Get toggle action (dialog)
			//Check if toggle action is modal
			if (toggle === 'dialog') {
				//Check if atribute exists
				if (atribute) {
					var modal = get(atribute); //Get modal
					var overlay = get('modal'); //Modal overlay
					overlay.style.display = "block"; //Set display block to overlay
					modal.style.display = "block"; //Set display block to modal
				}
			}
	});
	//Close Modal Box
	document.body.addEventListener("click",function(event){
		var target = event.target; //Set Event target
		var atribute = target.getAttribute('data-close');//Get data-close (modal id)
		var toggle = target.getAttribute('data-toggle');//Get data-toggle (dialog)
		var action = target.getAttribute('data-action');//Get data-action (close)
		
		//Check if the action is close
		if(action === 'close'){
			//Check if the attribute is dialog
			if(toggle === 'dialog'){
				//Check if atribute exists
				if(atribute){
					var modal = get(atribute);//Get modal
					var overlay = get('modal');//Get Modal ovelay
					overlay.style.display = "none";//Set display none
					modal.style.display = "none";//Set display none	
				}
			}
		}
	});
}())