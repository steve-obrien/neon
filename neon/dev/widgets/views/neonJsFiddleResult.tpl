<html>
	<head>
		{neon_head}
		{registerAsset path="\\neon\\core\\assets\\CoreAsset"}
		<style>
			body { padding:10px; }
			{$style}
		</style>
	</head>
	<body>
		{$html}

		{neon_body_end}
		<script>
			window.onload = function() {
				{$script}
			}
		</script>
	</body>
</html>
