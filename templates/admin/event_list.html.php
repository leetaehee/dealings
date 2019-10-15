<p>
	<h3>[<?=TITLE_EVENT_STATUS?>]</h3>
</p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 7%;">
		<col style="width: 11%;">
		<col style="width: 35%;">
		<col style="width: 17%;">
		<col style="width: 17%;">
		<col style="width: 13%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>구분</th>
			<th>이벤트명</th>
			<th>시작일자</th>
			<th>종료일자</th>
			<th>종료여부</th>
		</tr>
	</thead>
	<tbody>
		<?php if($eventListCount > 0): ?>
			<?php foreach($CONFIG_EVENT_ARRAY as $key => $value): ?>
				<tr>
					<td><?=$value['seq']?></td>
					<td><?=$value['event_type']?></td>
					<td>
						<a href="<?=$issueEventResultURL?>?idx=<?=$value['idx']?>&event_type=<?=$value['event_type']?>">
							<?=$value['event_name']?>
						</a>
					</td>
					<td><?=$value['start_date']?></td>
					<td><?=$value['end_date']?></td>
					<td><?=$value['is_end']=='N' ? '진행중' : '진행완료' ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="6" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>