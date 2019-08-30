<table class="table mileage-table-width">
	<colgroup>
		<col style="width: 6%;">
		<col style="width: 35%;">
		<col style="width: 15%;">
		<col style="width: 30%;">
		<col style="width: 14%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>이름(아이디)</th>
			<th>결제일</th>
			<th>사용가능 마일리지</th>
			<th>상태</th>
		</tr>
	</thead>
	<tbody>
		<?php if(count($members) > 0): ?>
			<?php foreach($members as $index => $member): ?>
				<tr>
					<td><?=$index+1?></td>
					<td>
						<?=setDecrypt($member['name'])?>
						(<?=$member['id']?>)
					</td>
					<td><?=$member['charge_date']?></td>
					<td><?=number_format($member['spare_cost'])?>원</td>
					<td><?=$member['mileage_name']?></td>
				</tr>
			<?php endforeach;?>
		<?php else: ?>
			<tr>
				<td colspan="4">회원이 존재하지 않습니다.</td>
			</tr>
		<?php endif;?>
	</tbody>
</table>