function getBOSHText (elem)
{
	if (!elem) return null;

	var str = "";
	if (elem.childNodes.length === 0 && elem.nodeType == 3) {
		str += elem.nodeValue;
	}

	for (var i = 0; i < elem.childNodes.length; i++) {
		if (elem.childNodes[i].nodeType == 3) {
			str += elem.childNodes[i].nodeValue;
		}
	}

	return str;
}

JSJaCIQ.prototype.setSubscribe = function(node, jid) {
	this.setType('set');

	var pNode;
	try {
	pNode = this.getDoc().createElementNS('http://jabber.org/protocol/pubsub','pubsub');
	} catch (e) {
	// fallback
	pNode = this.getDoc().createElement('pubsub');
	}
	if (pNode && pNode.getAttribute('xmlns') != 'http://jabber.org/protocol/pubsub') // fix opera 8.5x
    pNode.setAttribute('xmlns', 'http://jabber.org/protocol/pubsub');
	
	var aNode = this.getDoc().createElement('subscribe');
	aNode.setAttribute('node', node);
	aNode.setAttribute('jid', jid);
	
	pNode.appendChild(aNode);
	this.getNode().appendChild(pNode);
}