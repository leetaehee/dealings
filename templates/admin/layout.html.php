<!doctype html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title><?=$title?></title>

		<link rel="stylesheet" href="<?=NOMALIZE_CSS_URL;?>">
		<style>
			body { text-align: center; width: 1300px; }
			div#wrapper { width: 100%; text-align: left; min-height: 300px; margin: 0 auto;}
			header, footer, nav, aside, section { border: 1px solid #999; margin: 5px; padding: 10px; }
			nav { height: 30px;}
			header { height: 50px; }
			section { width: 1270px; height: 720px; overflow: scroll; }
			.ul-nav { list-style: none; margin: 0; padding: 0; }
			.ul-nav li { margin: 0 0 0 0; padding: 0 0 0 0; border: 0; float: left; }

			.table {
				font-size: 4pt;
				border: 1px solid #444444;
				border-collapse: collapse;
			}

			.admin-table-width {
				width: 1090px;
			}
			
			.mileage-table-width {
				width: 620px;
			}

			.table th, .table td {
				border: 1px solid #444444;
				padding: 2px;
			}

			.empty-tr-colspan {
				text-align: center;
				color: red;
			}
		</style>
    </head>
    <body>
		<div id="wrapper">
			<header>
				<h1>상품권 거래 사이트 (관리자)</h1>
			</header>
			<nav>
				<ul class="ul-nav">
					<?php if(isset($_SESSION['mIdx'])): ?>
						<li>
							<a href="<?=SITE_ADMIN_DOMAIN?>/admin.php">Home|</a>
						</li>
						<li>
							<a href="<?=SITE_ADMIN_DOMAIN?>/logout.php">로그아웃|</a>
						</li>
						<li>
							<a href="<?=SITE_ADMIN_DOMAIN?>/coupon.php">쿠폰관리|</a>
						</li>
						<li> 
							<a href="<?=SITE_ADMIN_DOMAIN?>/admin_event.php">이벤트|</a>
						</li>
						<li> 
							<a href="<?=SITE_ADMIN_DOMAIN?>/admin_page.php">관리자</a>
						</li>
					<?php else: ?>
						<li>
							<a href="<?=SITE_ADMIN_DOMAIN?>/index.php">Home|</a>
						</li>
						<li>
							<a href="<?=SITE_ADMIN_DOMAIN?>/login.php">로그인|</a>
						</li>
						<li>
							<a href="<?=SITE_ADMIN_DOMAIN?>/join.php">회원가입</a>
						</li>
					<?php endif; ?>
				</ul>
			</nav>
			<section>
				<?php include_once $templateFileName?>
			</section>
			<footer>
				&copy; 상품권 거래 시스템 <?=date('Y')?> <br>
				사이트에 오류가 있을 경우 관리자에게 문의바랍니다.
			</footer>
		</div>
		<script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
		<script>
			var ajaxUrl = "<?=$ajaxUrl?>";
		</script>
		<script src="<?=COMMON_JS_ADMIN_URL;?>"></script>
		<?php if(!empty($JsTemplateUrl)): ?>
			<script src="<?=$JsTemplateUrl;?>"></script>
		<?php endif; ?>
    </body>
</html>