 $(document).ready(function () {

	$(".ts-sidebar-menu li").each(function () {
		var $item = $(this);
		var $link = $item.children('a').first();
		var hasSubmenu = $item.children('ul').length > 0;

		if (hasSubmenu) {
			$link.addClass("parent");
			if ($item.children('.more').length === 0) {
				$('<div class="more"><i class="fa fa-angle-down"></i></div>').insertBefore($link);
			}
		}
	});

	$('.ts-sidebar-menu').on('click', '.more', function () {
		var $item = $(this).parent('li');
		$item.toggleClass('open');
		$item.children('a.parent').attr('aria-expanded', $item.hasClass('open') ? 'true' : 'false');
	});

	$('.ts-sidebar-menu').on('click', 'a.parent', function (e) {
		e.preventDefault();
		var $item = $(this).parent('li');
		$item.toggleClass('open');
		$(this).attr('aria-expanded', $item.hasClass('open') ? 'true' : 'false');
	});

	$('.menu-btn').click(function () {
		$('nav.ts-sidebar').toggleClass('menu-open');
	});
	 
	 
	 $('#zctb').DataTable();
	 
	 
	 $("#input-43").fileinput({
		showPreview: false,
		allowedFileExtensions: ["zip", "rar", "gz", "tgz"],
		elErrorContainer: "#errorBlock43"
			// you can configure `msgErrorClass` and `msgInvalidFileExtension` as well
	});

 });
