<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title><?php echo $title; ?></title>
	<style type="text/css">
		#outlook a {
			padding : 0;
		}
		body {
			-webkit-text-size-adjust : none;
			width                    : 100% !important;
			margin                   : 0;
			padding                  : 0;
			background               : #efefef;
			font-family              : Arial, serif;
		}
		#background-table {
			height  : 100% !important;
			margin  : 0;
			padding : 0;
			width   : 100% !important;
		}
		#container {
			border                : 1px solid #DDDDDD;
			-webkit-box-shadow    : 0 0 0 3px rgba(0, 0, 0, 0.1);
			-webkit-border-radius : 6px;
		}
		h1, .h1 {
			color       : #666666;
			display     : block;
			font-size   : 20px;
			font-weight : bold;
			line-height : 150%;
			margin      : 0 0 10px;
			text-align  : left;
		}
		h2 {
			font-size: 18px;
			font-weight: bold;
			margin-bottom: 10px;
			color: #666666;
		}
		h3{
			font-size: 1.1em !important;
			font-weight: bold !important;
			margin: 5px 0 !important;
			color: #444444;
		}
		p {
			padding : 0;
			margin  : 0;
		}
		#header {
			background                      : #FFF;
			border-bottom                   : 0;
			-webkit-border-top-left-radius  : 6px;
			-webkit-border-top-right-radius : 6px;
		}
		#header .content {
			color          : #202020;
			font-weight    : bold;
			line-height    : 100%;
			padding        : 10px;
			vertical-align : middle;
			max-height     : 150px;
		}
		#body {
			background  : #fff;
			color       : #505050;
			font-size   : 14px;
			line-height : 150%;
			text-align  : left !important;
		}
		a {
			color           : #de6b54;
			font-weight     : normal;
			text-decoration : none;
		}
		a:hover {
			color : #89230f;
		}
		#body img {
			display : inline;
			height  : auto;
		}
		#footer {
			background-color      : #2f3033;
			border-top            : 0;
			-webkit-border-radius : 6px;
		}
		#title .content {
			font-size   : 14px;
			line-height : 125%;
			background  : #2f3033 none repeat scroll 0 0;
			width       : 100%;
			color       : #FFF;
			text-align  : center;
			height      : 15px;
		}
		#footer .content {
			font-size   : 11px;
			line-height : 125%;
			background  : #2f3033 none repeat scroll 0 0;
			width       : 100%;
			color       : #aaaaaa;
			text-align  : center;
		}
		#footer .content img {
			display : inline;
		}
		#credit {
			border      : 0;
			color       : #707070;
			font-size   : 12px;
			line-height : 125%;
			text-align  : center;
		}
		.automated {
			text-align : center;
			font-size  : 12px;
			color      : #B2B2B2;
			margin     : 10px 0 0 0;
		}
		#header .tag {
			color      : #4b4b4b;
			font-style : italic;
		}

	</style>
</head>
<body>
<div style="font-size: 1px; color:#ffffff; display:none; overflow:hidden; visibility:hidden;">
	<?php echo $title; ?>
</div>
<table id="background-table" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
	<tr>
		<td align="center" valign="top">
			<table id="container" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td align="center" valign="top">
						<table id="header" border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td class="content">
									<table border="0" cellpadding="10" cellspacing="0" width="100%">
										<tr>
											<td valign="middle" align="left">
												<img src="<?php echo C_TRAX_INTEGRATION_URL; ?>images/logo.png"/>
											</td>
											<td valign="middle" align="right">
												<p class="tag"><?php echo $company; ?></p>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" valign="top">
						<table id="title" border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td class="content">
									<table border="0" cellpadding="10" cellspacing="0" width="100%">
										<tr>
											<td valign="middle" align="center">

											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" valign="top">
						<table id="body" border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td class="content" valign="top">
									<table border="0" cellpadding="20" cellspacing="0" width="100%">
										<tr>
											<td valign="top">
												<div>
													<?php echo $content; ?>
												</div>
												<br/>

												<p class="automated">Automated Email - Do Not Reply</p>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" valign="top">
						<table id="footer" border="0" cellpadding="10" cellspacing="0" width="100%">
							<tr>
								<td class="content" valign="top">
									<table border="0" cellpadding="10" cellspacing="0" width="100%">
										<tr>
											<td colspan="2" valign="middle" id="credit">
												<p>&copy; <?php echo date('Y'); ?> <a href="<?php echo get_site_url(); ?>"><?php echo $company; ?></a></p>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<br/>
		</td>
	</tr>
</table>
</body>
</html>















