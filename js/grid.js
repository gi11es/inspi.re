function onItemSelected(item) {
    if (item == null) {
        // nothing selected...place your code here
    } else {
        alert(item.guid); // replace with your own code
    }
}

var cooliris = {
    onEmbedInitialized : function() {
        cooliris.embed.setCallbacks({
            select: onItemSelected
        });
    }
};
