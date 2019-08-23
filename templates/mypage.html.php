<?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']=='N'): ?>
	<p>
		회원 탈퇴를 하시면 계정을 복구할 수 없습니다. 진행을 하시면 아래 버튼을 클릭하세요.<br>
		<a href="<?=$member_del_url?>?mode=del&idx=<?=$_SESSION['idx']?>">
			회원탈퇴하러가기
		</a>
	</p>
<?php else:?>
	<p>관리자는 회원 탈퇴를 하실 수 없습니다!!</p>
	<p>관리자는 최고관리자에게 연락하여 해제하시길 바랍니다.</p>
<?php endif; ?>