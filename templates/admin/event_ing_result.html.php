<?php if($eventType == '판매'): ?>
    <form id="event-result-form" method="post" action="<?=$actionUrl?>">
        <input type="hidden" name="idx" value="<?=$getData['idx']?>">
        <p><h3>["<?=$eventName?>" <?=TITLE_EVENT_ING_RESULT?>]</h3></p>
        <p>
            - 이벤트 기간이 종료가 될 경우에 "지급하기" 버튼이 활성화 됩니다.<br>
            - 이벤트기간: <?=$eventStartDate?> ~ <?=$eventEndDate?>
        </p>
        <?php if($isReturnFeeProvider == true && $eventIsEnd == 'N'): ?>
            <input type="submit" id="btn-submit" value="일괄지급">
        <?php endif;?>
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
            <?php if($rEventHistoryDataCount > 0): ?>
                <?php foreach($rEventHistoryData as $key => $value): ?>
                    <tr>
                        <td><?=$value['seq']?></td>
                        <td><?=$value['name']?></td>
                        <td><?=$value['event_cost']?>원</td>
                        <td><?=$value['return_fee']?>%</td>
                        <td><?=$value['return_fee_cost']?>원</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="empty-tr-colspan">내역이 없습니다.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>
<?php else: ?>
    <p>판매이벤트만 결과를 조회 할 수 있습니다.</p>
<?php endif; ?>