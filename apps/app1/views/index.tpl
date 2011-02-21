<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>{$title}</title>
	</head>
	<body>
		<h1>{$title}</h1>
		<ul>
			{foreach item=i from=$items}
				<li><strong>{$i.title}</strong> &mdash; {$i.description}</li>
			{/foreach}
		</ul>
	</body>
</html>