<p><h3>[내 정보]</h3></p>
<p>---------------------------------------</p>
<p>1. 계정명: <?=$id?></p>
<p>2. 회원명: <?=$name?>(<?=$sex?>)</p>
<p>3. 생년월일: <?=$birth?></p>
<p>4. 이메일: <?=$email?></p>
<p>5. 핸드폰: <?=$phone?></p>
<p>6. 회원가입일: <?=$joinDate?>(메일승인일자: <?=$joinApprovalDate?>)</p>
<p>7. 마일리지: <?=$mileage?>원</p>
<?php if(!empty($accountNo)): ?>
	<p>8. 내 계좌 정보: <?=$accountNo?>(<?=$accountBank?>)</p>
<?php else: ?>
	<p>8. 내 계좌 정보: 미설정 </p>
<?php endif; ?>
<p>9. 활동등급: <?=$gradeName?></p><br>
<span>
    <a href="<?=$memberModifyUrl?>">[정보수정]</a>
</span>
<span class="pl">
    <a href="<?=$memberDeleteUrl?>">[회원탈퇴]</a>
</span>
<span class="pl">
    <a href="<?=$myAccountSetUrl?>">[출금계좌설정]</a>
</span>
