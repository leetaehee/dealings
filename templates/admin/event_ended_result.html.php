<?php if($eventType == '판매'): ?>
	<input type="hidden" name="idx" value="<?=$getData['idx']?>">
	<p>
		<h3>["<?=$eventName?>" <?=TITLE_EVENT_ENDED_RESULT?>]</h3>
	</p>

	<p>
		- 이벤트기간: <?=$eventStartDate?> ~ <?=$eventEndDate?>
	</p>


	<table class="table event-result-table-width">
		<colgroup>
			<col style="width: 10%;">
			<col style="width: 25%;">
			<col style="width: 30%;">
			<col style="width: 10%;">
			<col style="width: 25%;">
		</colgroup>
		<thead>
			<tr>
				<th>순번</th>
				<th>이름</th>
				<th>수수료 총 금액</th>
				<th>환급률</th>
				<th>환급금액</th>
			</tr>
		</thead>
		<tbody>
			<?php if($eventHistoryListCount > 0): ?>
				<?php foreach($eventHistoryList as $key => $value): ?>
					<tr>
						<td><?=$key+1?></td>
						<td><?=setDecrypt($value['name'])?></td>
						<td><?=number_format($value['event_cost'])?>원</td>
						<td><?=$CONFIG_EVENT_SELL_RETRUN_FEE[$key+1]?>%</td>
						<td>
							<?=number_format(($value['event_cost']*$CONFIG_EVENT_SELL_RETRUN_FEE[$key+1])/100)?>원
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr>
					<td colspan="5" class="empty-tr-colspan">내역이 없습니다.</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
<?php else: ?>
	<p>판매이벤트만 결과를 조회 할 수 있습니다.</p>
<?php endif; ?>