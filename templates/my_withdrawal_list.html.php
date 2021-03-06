<p><h3>[마일리지 출금내역]</h3></p>
<table class="table charge-table-width">
	<colgroup>
		<col style="width: 8%;">
		<col style="width: 14%;">
		<col style="width: 18;">
		<col style="width: 30%;">
		<col style="width: 14%;">
		<col style="width: 8%;">
		<col style="width: 9%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>출금일자</th>
			<th>출금정보</th>
			<th>출금고유번호</th>
			<th>출금금액</th>
			<th>상태</th>
			<th>출금자</th>
		</tr>
	</thead>
	<tbody>
		<?php if($myWithdrawalDataCount > 0): ?>
			<?php foreach($myWithdrawalData as $key => $value): ?>
				<tr>
					<td><?=$value['seq']?></td>
					<td><?=$value['process_date']?></td>
					<td><?=$value['charge_infomation']?></td>
					<td>
						<?=setDecrypt($value['charge_account_no'])?>
						(<?=$value['charge_taget_name']?>)
					</td>
					<td><?=number_format($value['charge_cost'])?></td>
					<td><?=$value['mileage_code_name']?></td>
					<td><?=$value['charge_name']?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="7" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>