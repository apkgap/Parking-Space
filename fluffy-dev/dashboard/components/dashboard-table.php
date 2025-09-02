<?php
/**
 * Dashboard Table Component
 * Reusable responsive table component for dashboard data
 */

function render_dashboard_table($table_id, $columns, $data, $actions = []) {
    ?>
    <div class="overflow-x-auto">
        <table id="<?php echo esc_attr($table_id); ?>" class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
            <thead class="bg-gray-50">
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <?php echo esc_html($column['label']); ?>
                        </th>
                    <?php endforeach; ?>
                    <?php if (!empty($actions)): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="<?php echo count($columns) + (empty($actions) ? 0 : 1); ?>" 
                            class="px-6 py-4 text-center text-gray-500">
                            No data available
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <?php foreach ($columns as $column): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php 
                                    $value = isset($row[$column['key']]) ? $row[$column['key']] : '';
                                    if (isset($column['format']) && is_callable($column['format'])) {
                                        echo $column['format']($value, $row);
                                    } else {
                                        echo esc_html($value);
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if (!empty($actions)): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php foreach ($actions as $action): ?>
                                        <button type="button" 
                                                class="<?php echo esc_attr($action['class'] ?? 'text-indigo-600 hover:text-indigo-900'); ?> mr-2"
                                                onclick="<?php echo esc_attr($action['onclick'] ?? ''); ?>"
                                                data-id="<?php echo esc_attr($row['id'] ?? ''); ?>">
                                            <?php echo esc_html($action['label']); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Render dashboard stats cards
 */
function render_dashboard_stats($stats) {
    ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php foreach ($stats as $stat): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <?php echo $stat['icon'] ?? ''; ?>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                <?php echo esc_html($stat['label']); ?>
                            </dt>
                            <dd class="text-lg font-medium text-gray-900">
                                <?php echo esc_html($stat['value']); ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Render dashboard filter form
 */
function render_dashboard_filters($filters, $form_id = 'dashboard-filters') {
    ?>
    <form id="<?php echo esc_attr($form_id); ?>" class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($filters as $filter): ?>
                <div>
                    <label for="<?php echo esc_attr($filter['id']); ?>" 
                           class="block text-sm font-medium text-gray-700 mb-1">
                        <?php echo esc_html($filter['label']); ?>
                    </label>
                    <?php if ($filter['type'] === 'select'): ?>
                        <select id="<?php echo esc_attr($filter['id']); ?>" 
                                name="<?php echo esc_attr($filter['name']); ?>"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <?php foreach ($filter['options'] as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>">
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($filter['type'] === 'date'): ?>
                        <input type="date" 
                               id="<?php echo esc_attr($filter['id']); ?>"
                               name="<?php echo esc_attr($filter['name']); ?>"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <?php else: ?>
                        <input type="<?php echo esc_attr($filter['type'] ?? 'text'); ?>" 
                               id="<?php echo esc_attr($filter['id']); ?>"
                               name="<?php echo esc_attr($filter['name']); ?>"
                               placeholder="<?php echo esc_attr($filter['placeholder'] ?? ''); ?>"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="flex items-end">
                <button type="submit" 
                        class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Filter
                </button>
            </div>
        </div>
    </form>
    <?php
}
?>
