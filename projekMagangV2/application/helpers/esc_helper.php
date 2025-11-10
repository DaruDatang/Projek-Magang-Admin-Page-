<?php
function esc($v) {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}