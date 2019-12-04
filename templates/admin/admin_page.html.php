<p><h3>[내 정보]</h3></p>
<p>---------------------------------------</p>
<p>1. 계정명: <?=$id?></p>
<p>2. 회원명: <?=$name?>(<?=$sex?>)</p>
<p>3. 생년월일: <?=$birth?></p>
<p>4. 이메일: <?=$email?></p>
<p>5. 핸드폰: <?=$phone?></p>
<p>6. 회원가입일: <?=$joinDate?> (메일승인일자: <?=$joinApprovalDate?>)</p>
<?php if($isSuperAdmin == 'Y'): ?>
	<p>7. 슈퍼 관리자 여부:  설정</p>
<?php else: ?>
	<p>7. 슈퍼 관리자 여부: 미설정</p>
<?php endif; ?>
<span>
    <a href="<?=$adminModifyUrl?>">[관리자 정보수정]</a>
</span>
<span class="pl">
    <a href="<?=$adminDeleteUrl?>">[관리자 탈퇴]</a>
</span>