<?php
// Determine the current month and prepare dates
$dates = [];
$currentDate = clone $currentMonth;
$endDate = (clone $currentDate)->modify('+1 month');
while ($currentDate < $endDate) {
    $dates[] = $currentDate->format('Y-m-d');
    $currentDate->modify('+1 day');
}

// Render the month
?>

<div class="month-section" data-month="<?= $currentMonth->format('Y-m'); ?>">
    <h3 class="month-heading"><?= $currentMonth->format('F Y'); ?></h3>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th style="position: sticky; left: 0; background: #343a40; color: #fff; z-index: 10;">Date</th>
                    <?php foreach ($stores as $store): ?>
                        <th style="writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap; position: sticky; top: 0; background: #343a40; color: #fff; z-index: 10;">
                            <?= htmlspecialchars($store['name']); ?><br>
                            <?= htmlspecialchars($store['location']); ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dates as $date): ?>
                    <?php 
                    $dateTime = new DateTime($date);
                    $weekday = $dateTime->format('D');
                    $formattedDate = $dateTime->format('d.m.y');
                    $isMonday = $dateTime->format('l') === 'Monday';
                    ?>
                    <tr style="<?= $isMonday ? 'background-color: #f2f2f2;' : ''; ?>">
                        <td style="position: sticky; left: 0; background: #fff; z-index: 5;"><?= $weekday . '. ' . $formattedDate; ?></td>
                        <?php foreach ($stores as $store): ?>
                            <td class="droppable" 
                                data-store-id="<?= $store['id']; ?>" 
                                data-date="<?= $date; ?>" 
                                id="droppable-<?= $store['id'] . '-' . $date; ?>"
                                style="background-color: #ffffff; position: relative;">
                                <ul class="store-list list-unstyled d-flex flex-wrap">
                                    <?php foreach ($assignments as $assignment): ?>
                                        <?php if ($assignment['store_id'] == $store['id'] && $assignment['date'] == $date): ?>
                                            <li class="p-2 m-1 text-white rounded-pill draggable-item" 
                                                style="background-color: <?= htmlspecialchars($assignment['volunteer_color']); ?>; position: relative;" 
                                                data-volunteer-id="<?= $assignment['volunteer_id']; ?>"
                                                data-store-id="<?= $store['id']; ?>"
                                                data-date="<?= $date; ?>"
                                                draggable="true">
                                                <?= htmlspecialchars($assignment['volunteer_name']); ?>
                                                <button class="delete-assignment-btn">&times;</button>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>