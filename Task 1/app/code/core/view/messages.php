<div id="notificationMsg">
<?php $hasSuccesses = isset($messages[REQUEST_MESSAGE_TYPE_SUCCESS]); ?>
        <div id="notificationMsgSuccess"<?php echo ($hasSuccesses ? ' style="display: block;"' : ''); ?>>
<?php if ($hasSuccesses):
        foreach ($messages[REQUEST_MESSAGE_TYPE_SUCCESS] as $message):
                echo $message; ?>
                <br>
<?php   endforeach;
      endif; ?>
        </div>
<?php $hasNotices = isset($messages[REQUEST_MESSAGE_TYPE_NOTICE]); ?>
        <div id="notificationMsgNotice"<?php echo ($hasNotices ? ' style="display: block;"' : ''); ?>>
<?php if ($hasNotices):
        foreach ($messages[REQUEST_MESSAGE_TYPE_NOTICE] as $message):
                echo $message; ?>
                <br>
<?php   endforeach;
      endif; ?>
        </div>
<?php $hasErrors = isset($messages[REQUEST_MESSAGE_TYPE_ERROR]); ?>
        <div id="notificationMsgError"<?php echo ($hasErrors ? ' style="display: block;"' : ''); ?>>
<?php if ($hasErrors):
        foreach ($messages[REQUEST_MESSAGE_TYPE_ERROR] as $message):
                echo $message; ?>
                <br>
<?php   endforeach;
      endif; ?>
        </div>
</div>