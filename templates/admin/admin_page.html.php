<p><h3>[내 정보]</h3></p>
<p>---------------------------------------</p>
<p>
    1. 계정명: <?=$adminData->fields['id']?>
</p>
<p>
    2. 회원명: <?=setDecrypt($adminData->fields['name'])?>
    (<?=$adminData->fields['sex_name']?>)
</p>
<p>
    3. 생년월일: <?=setDecrypt($adminData->fields['birth'])?>
</p>
<p>
    4. 이메일: <?=setDecrypt($adminData->fields['email'])?>
</p>
<p>
    5. 핸드폰: <?=setDecrypt($adminData->fields['phone'])?>
</p>
<p>
    6. 회원가입일: <?=$adminData->fields['join_date']?>
    (메일승인일자: <?=$adminData->fields['join_approval_date']?>)
</p>
<?php if($adminData->fields['is_superadmin']=='Y'): ?>
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