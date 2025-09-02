<?php
/**
 * Dashboard Modal Component
 * Reusable modal component for dashboard forms and confirmations
 */

function render_dashboard_modal($modal_id, $title, $content = '', $size = 'md') {
    $size_classes = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl'
    ];
    
    $modal_size = $size_classes[$size] ?? $size_classes['md'];
    ?>
    <div id="<?php echo esc_attr($modal_id); ?>" 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 <?php echo esc_attr($modal_size); ?> shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-lg font-bold text-gray-900">
                    <?php echo esc_html($title); ?>
                </h3>
                <button type="button" 
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                        onclick="closeModal('<?php echo esc_js($modal_id); ?>')">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render confirmation modal
 */
function render_confirmation_modal($modal_id, $title, $message, $confirm_text = 'Confirm', $cancel_text = 'Cancel') {
    ob_start();
    ?>
    <div class="text-center">
        <p class="text-gray-600 mb-6"><?php echo esc_html($message); ?></p>
        <div class="flex justify-center space-x-4">
            <button type="button" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                    onclick="closeModal('<?php echo esc_js($modal_id); ?>')">
                <?php echo esc_html($cancel_text); ?>
            </button>
            <button type="button" 
                    id="<?php echo esc_attr($modal_id); ?>-confirm"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                <?php echo esc_html($confirm_text); ?>
            </button>
        </div>
    </div>
    <?php
    $content = ob_get_clean();
    
    render_dashboard_modal($modal_id, $title, $content, 'sm');
}

/**
 * Render form modal
 */
function render_form_modal($modal_id, $title, $form_fields, $submit_text = 'Save') {
    ob_start();
    ?>
    <form id="<?php echo esc_attr($modal_id); ?>-form" class="space-y-4">
        <?php foreach ($form_fields as $field): ?>
            <div>
                <label for="<?php echo esc_attr($field['id']); ?>" 
                       class="block text-sm font-medium text-gray-700 mb-1">
                    <?php echo esc_html($field['label']); ?>
                    <?php if (isset($field['required']) && $field['required']): ?>
                        <span class="text-red-500">*</span>
                    <?php endif; ?>
                </label>
                
                <?php if ($field['type'] === 'textarea'): ?>
                    <textarea id="<?php echo esc_attr($field['id']); ?>"
                              name="<?php echo esc_attr($field['name']); ?>"
                              rows="<?php echo esc_attr($field['rows'] ?? 3); ?>"
                              placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?>></textarea>
                              
                <?php elseif ($field['type'] === 'select'): ?>
                    <select id="<?php echo esc_attr($field['id']); ?>"
                            name="<?php echo esc_attr($field['name']); ?>"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?>>
                        <?php foreach ($field['options'] as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>">
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                <?php else: ?>
                    <input type="<?php echo esc_attr($field['type'] ?? 'text'); ?>"
                           id="<?php echo esc_attr($field['id']); ?>"
                           name="<?php echo esc_attr($field['name']); ?>"
                           placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?>>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="flex justify-end space-x-4 pt-4">
            <button type="button" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                    onclick="closeModal('<?php echo esc_js($modal_id); ?>')">
                Cancel
            </button>
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                <?php echo esc_html($submit_text); ?>
            </button>
        </div>
    </form>
    <?php
    $content = ob_get_clean();
    
    render_dashboard_modal($modal_id, $title, $content);
}
?>
