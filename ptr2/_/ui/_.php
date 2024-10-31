<?php
namespace Plainware;

return [
	// [ '*::nav*', HtmlMenu::class . '::finalize', 8 ],
	[ Layout::class . '::render', LayoutPrint::class . '::render' ],
];