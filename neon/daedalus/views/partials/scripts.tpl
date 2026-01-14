<script type="text/javascript">
	var daedalusSetChangeLog = function(to, goTo) {
		var message = "Turning on the change log will use up more server resources. You should only turn it on if required. Are you sure?";
		if (to === 0)
			message = "The change log for this table may be required by the application. Only turn it off if you are certain this won't cause problems. Are you sure?";
		var doSet = confirm(message);
		if (doSet) {
			$.ajax({
				url:"{url route='/daedalus/change-log/set' type=$type}"+"&to="+to,
				goTo: goTo,
				success: function() {
					if (this.goTo !== undefined)
						document.location = this.goTo;
					else
						document.location.reload();
				},
				error: function() { alert("Sorry it was not possible to change the setting."); }
			});
		}
	};
</script>
