<p><h3>[<?=TITLE_ADMIN_CHARGE_STATUS?>]</h3></p>
<p>
	<h5>- 충전 후 사용금액이 존재하는 경우 취소를 할 수 없습니다!</h5>
	<h5>- 취소 한 내역은 다시 복구 할 수 없습니다.!</h5>
	<h5>- 아이템 거래를 통해 들어온 금액은 취소 할 수 없습니다!</h5>
</p>
<table class="table charge-table-width">
	<colgroup>
		<col style="width: 7%;">
		<col style="width: 11%;">
		<col style="width: 15%;">
		<col style="width: 20%;">
		<col style="width: 16%;">
		<col style="width: 11%;">
		<col style="width: 12%;">
		<col style="width: 9%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>이름(id)</th>
			<th>연락처</th>
			<th>결제정보</th>
			<th>결제일</th>
			<th>결제금액</th>
			<th>사용금액</th>
			<th>취소</th>
		</tr>
	</thead>
	<tbody>
		<?php if($rocordCount > 0): ?>
			<?php foreach($chageList as $key => $value): ?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=setDecrypt($value['name'])?><br>(<?=$value['id']?>)</td>
					<td><?=setDecrypt($value['phone'])?></td>
					<td>
						[<?=$value['charge_taget_name']?>]
						<?=$value['charge_infomation']?><br>
						(<?=setDecrypt($value['charge_account_no'])?>)
					</td>
					<td><?=$value['charge_date']?></td>
					<td><?=number_format($value['charge_cost'])?></td>
					<td><?=number_format($value['use_cost'])?></td>
					<td>
						<?php if($value['use_cost'] == 0): ?>
							<!-- 아래 값을 비교할 때 php 배열로 정의 후 in_array 함수로 개선하도록 수정 필요. --> 
							<?php if($value['charge_target_idx']!=7 && $value['charge_target_idx']!=8): ?>
								<a href="<?=$chargeCancelUrl?>?idx=<?=$value['idx']?>">
									[취소]
								</a>
							<?php endif; ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="8" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>