<p><h3>[마일리지 충전내역]</h3></p>
<table class="table charge-table-width">
	<colgroup>
		<col style="width: 6%;">
		<col style="width: 11%;">
		<col style="width: 15%;">
		<col style="width: 28%;">
		<col style="width: 11%;">
		<col style="width: 11%;">
		<col style="width: 9%;">
		<col style="width: 9%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>충전일자</th>
			<th>충전정보</th>
			<th>충전고유번호</th>
			<th>충전마일리지</th>
			<th>사용마일리지</th>
			<th>상태</th>
			<th>충전자</th>
		</tr>
	</thead>
	<tbody>
		<?php if($chargeCount > 0): ?>
			<?php foreach($chargeData as $key => $value): ?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=$value['charge_date']?></td>
					<td><?=$value['charge_infomation']?></td>
					<td>
						<?=setDecrypt($value['charge_account_no'])?>
						(<?=$value['charge_taget_name']?>)
					</td>
					<td><?=number_format($value['charge_cost'])?></td>
					<td><?=number_format($value['use_cost'])?></td>
					<td><?=$value['mileage_name']?></td>
					<td><?=$value['charge_name']?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="8" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>