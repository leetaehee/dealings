<p><h3>[내 정보]</h3></p>
<p>---------------------------------------</p>
<p>
    1. 계정명: <?=$myInfomation->fields['id']?>
</p>
<p>
    2. 회원명: <?=setDecrypt($myInfomation->fields['name'])?>
    (<?=$myInfomation->fields['sex_name']?>)
</p>
<p>
    3. 생년월일: <?=setDecrypt($myInfomation->fields['birth'])?>
</p>
<p>
    4. 이메일: <?=setDecrypt($myInfomation->fields['email'])?>
</p>
<p>
    5. 핸드폰: <?=setDecrypt($myInfomation->fields['phone'])?>
</p>
<p>
    6. 회원가입일: <?=$myInfomation->fields['join_date']?>
    (메일승인일자: <?=$myInfomation->fields['join_approval_date']?>)
</p>
<p>
	7. 마일리지: <?=number_format($myInfomation->fields['mileage'])?>원
</p>
<?php if(!empty($myInfomation->fields['account_no'])): ?>
<p>
	8. 내 계좌 정보: 
	<?=setDecrypt($myInfomation->fields['account_no'])?>(<?=$myInfomation->fields['account_bank']?>)
</p>
<?php else: ?>
	8. 내 계좌 정보: 미설정
<?php endif; ?>
<span>
    <a href="<?=$memberModifyUrl?>">[정보수정]</a>
</span>
<span class="pl">
    <a href="<?=$memberDeleteUrl?>">[회원탈퇴]</a>
</span>
<span class="pl">
    <a href="<?=$myAccountSetUrl?>">[출금계좌설정]</a>
</span>
