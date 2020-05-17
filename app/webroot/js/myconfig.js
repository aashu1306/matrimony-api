$('#confirmModal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var modal_title = button.data('title'); // Extract info from data-* attributes
		var modal_body = button.data('body');
		var modal_btn = button.data('button');
		var btn_event = button.attr('link');
		// If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
		// Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
		
		// Target Logic
		
		var target = get_target(button);

		var modal = $(this);
		modal.find('.modal-title').text(modal_title);
		modal.find('.modal-body').text(modal_body);
		modal.find('.modal-button').text(modal_btn);

		modal.find('.modal-button').unbind('click');
		modal.find('.modal-button').bind('click', function() {
			$(target).load(btn_event);
			modal.modal('hide');
		});
	});
$('#formModalSm').on('loaded.bs.modal', function (event) {
});