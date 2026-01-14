<template>
	<section class="callout">
		<div class="callout_images">
			<div class="callout_image">
				<img src='editor.srv("asset", {"path":"images/temp/callout-image-1.svg"})' alt="" width="277" height="209">
			</div><!-- /.callout_image -->

			<div class="callout_image">
				<img src="{asset path='images/temp/callout-image-2.png'}" alt="" width="222" height="222"></a>
			</div><!-- /.callout_image -->

			<div class="callout_image">
				<img src="{asset path='images/temp/callout-image-3.png'}" alt="" width="145" height="68">
			</div><!-- /.callout_image -->
		</div><!-- /.callout_images -->

		<div class="callout_content">
			<div class="shell">
				<div class="callout_inner">
					<div class="callout_head">
						<h1 class="callout_title">Talk to us&hellip;</h1><!-- /.callout_title -->

						<h4 class="callout_subtitle">From Digital Transformation to Augmented Reality<br>we can help you build your future.</h4><!-- /.callout_subtitle -->
						<p>To discuss digital transformation in your business or startup &hellip;</p>
						<a href="#" class="btn btn-white">Send us a message</a> <a href="" class="btn btn-gray btn-outline" onclick="Calendly.initPopupWidget({ 'url': 'https://calendly.com/mark-probert/30-mins-call' });return false;">Book a call with Mark</a>
					</div><!-- /.callout_head -->

					<div class="callout_body">
						<ul class="list-contacts">
							<li>
								<h5>Call us on:</h5>
								<a href="tel:01172050425">0117 205 0425</a>
							</li>
							<li>
								<h5>Email us at:</h5>
								<a href="mailto:talktous@newicon.net">talktous@newicon.net</a>
							</li>
						</ul>
					</div><!-- /.callout_body -->
				</div><!-- /.callout_inner -->
			</div><!-- /.shell -->
		</div><!-- /.callout_content -->
	</section><!-- /.callout -->
</template>

<script>
	export default {
		name: "NeonComeTalkToUs"
	}
</script>

<style scoped>

</style>