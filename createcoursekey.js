/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function updateGroupselectboxStatus() {
    if ($('#group').val() == 'new') {
        document.getElementById('groupname').disabled = false;
    } else {
        document.getElementById('groupname').disabled = true;
    }
}