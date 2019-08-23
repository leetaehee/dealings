<!doctype html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title><?=$title?></title>
		<style>
			body {
				font-size: 9pt;
			}

			.table {
				width: 1090px;
				font-size: 4pt;
				border: 1px solid #444444;
				border-collapse: collapse;
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
        <header>
            <h1>IMI 회원정보 시스템</h1>
        </header>
        <nav>
            <ul>
                <li>
                    <a href="<?=SITE_DOMAIN?>/index.php">Home</a>
                </li>
				<?php if(isset($_SESSION['idx'])): ?>
					<li>
						<a href="<?=SITE_DOMAIN?>/logout.php">로그아웃</a>
					</li>
					<li> 
						<a href="<?=SITE_DOMAIN?>/mypage.php">마이페이지</a>
					</li>
				<?php else: ?>
					<li>
						<a href="<?=SITE_DOMAIN?>/login.php">로그인</a>
					</li>
					<li>
						<a href="<?=SITE_DOMAIN?>/join.php">회원가입</a>
					</li>
				<?php endif; ?>
				<?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']=='Y'): ?>
					<li>
						<a href="<?=SITE_DOMAIN?>/admin.php">관리자페이지</a>
					</li>
				<?php endif; ?>
            </ul>
        </nav>
        <main>
            <?=$output?>
        </main>
        <footer>
            &copy; IMI 회원정보 시스템 2019 <br>
			사이트에 오류가 있을 경우 관리자에게 문의바랍니다.
        </footer>
    </body>
</html>