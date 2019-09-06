<!doctype html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title><?=$title?></title>

		<link rel="stylesheet" href="<?=NOMALIZE_CSS_URL;?>">
		<style>
			body { text-align: center; width: 900px; }
			div#wrapper { width: 100%; text-align: left; min-height: 300px; margin: 0 auto;}
			header, footer, nav, aisde, section { border: 1px solid #999; margin: 5px; padding: 10px; }
			nav { height: 30px;}
			header { height: 50px; }
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
				<h1>IMI</h1>
			</header>
			<nav>
				<ul class="ul-nav">
					<?php if(isset($_SESSION['idx'])): ?>
						<li>
						<a href="<?=SITE_DOMAIN?>/imi.php">Home|</a>
					</li>
						<li>
							<a href="<?=SITE_DOMAIN?>/logout.php">로그아웃|</a>
						</li>
						<li> 
							<a href="<?=SITE_DOMAIN?>/mypage.php">마이룸</a>
						</li>
					<?php else: ?>
						<li>
							<a href="<?=SITE_DOMAIN?>/index.php">Home|</a>
						</li>
						<li>
							<a href="<?=SITE_DOMAIN?>/login.php">로그인|</a>
						</li>
						<li>
							<a href="<?=SITE_DOMAIN?>/join.php">회원가입</a>
						</li>
					<?php endif; ?>
				</ul>
			</nav>
			<section>
				<?=$output?>
			</section>
			<footer>
				&copy; IMI 시스템 <?=date('Y')?> <br>
				사이트에 오류가 있을 경우 관리자에게 문의바랍니다.
			</footer>
		</div>
		<script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
		<script>
			var ajaxUrl = "<?=$ajaxUrl?>";
		</script>
		<script src="<?=COMMON_JS_URL;?>"></script>
		<?php if(!empty($JsTemplateUrl)): ?>
			<script src="<?=$JsTemplateUrl;?>"></script>
		<?php endif; ?>
    </body>
</html>