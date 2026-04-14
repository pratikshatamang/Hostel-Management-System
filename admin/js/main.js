$(document).ready(function () {
	$(".ts-sidebar-menu li a").each(function () {
		if ($(this).next().length > 0) {
			$(this).addClass("parent");
		}
	});

	var menux = $('.ts-sidebar-menu li a.parent');
	if ($('.more').length === 0) {
		$('<div class="more"><i class="fa fa-angle-down"></i></div>').insertBefore(menux);
	}

	function setSidebarState(isOpen) {
		$('nav.ts-sidebar').toggleClass('menu-open', isOpen);
		$('body').toggleClass('admin-sidebar-open', isOpen);
		$('.admin-menu-toggle').attr('aria-expanded', isOpen ? 'true' : 'false');
	}

	$('.more').click(function () {
		$(this).parent('li').toggleClass('open');
	});

	$('.parent').click(function (e) {
		e.preventDefault();
		$(this).parent('li').toggleClass('open');
	});

	$('.menu-btn').click(function () {
		setSidebarState(!$('nav.ts-sidebar').hasClass('menu-open'));
	});

	$('.admin-sidebar-backdrop').click(function () {
		setSidebarState(false);
	});

	$(window).on('resize', function () {
		if (window.innerWidth >= 992) {
			setSidebarState(false);
		}
	});

	$('#zctb').DataTable();

	$("#input-43").fileinput({
		showPreview: false,
		allowedFileExtensions: ["zip", "rar", "gz", "tgz"],
		elErrorContainer: "#errorBlock43"
	});
});
