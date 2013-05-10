import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.URI;
import java.net.URLDecoder;
import java.util.concurrent.ConcurrentHashMap;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Set;
import java.util.Vector;
import com.sun.net.httpserver.Headers;
import com.sun.net.httpserver.HttpExchange;
import com.sun.net.httpserver.HttpHandler;
import org.json.JSONArray;
import org.json.JSONObject;

public class PollHandler implements HttpHandler {
	private static final int history = 20;
	private final Object lock = new Object();
	private static ConcurrentHashMap<String, Long> channelCurrentMessageId = new ConcurrentHashMap<String, Long>();
	private static ConcurrentHashMap<String, ConcurrentHashMap<Long, JSONObject>> channelMessage = new ConcurrentHashMap<String, ConcurrentHashMap<Long, JSONObject>>();
	private static ConcurrentHashMap<String, Vector<Thread>> channels = new  ConcurrentHashMap<String, Vector<Thread>>();
	
	public void write(String channelname, String text) {
		JSONObject message;
		Iterator<Thread> iter;
		
		channels.putIfAbsent(channelname, new Vector<Thread>());
		
		synchronized (channels.get(channelname)) {	
			long id = 0;
			
			channelMessage.putIfAbsent(channelname, new ConcurrentHashMap<Long, JSONObject>());
			channelCurrentMessageId.putIfAbsent(channelname, id);
			
			id = channelCurrentMessageId.get(channelname) + 1;
			channelCurrentMessageId.put(channelname, id);
				
			try {
				JSONObject json = new JSONObject();
				json.put("text", text);
				json.put("channel", channelname);
				json.put("message_id", id);
				
				message = json;
			} catch (Exception e) {
				message = new JSONObject();
			}
				
			channelMessage.get(channelname).put(id, message);
			
			// Only keep the last X messages in a given channel
			if (channelMessage.get(channelname).size() > history) {
				for (long i = id - channelMessage.get(channelname).size() + 1; i <= id - history; i++)
					channelMessage.get(channelname).remove(i);
			}
			
			iter = channels.get(channelname).iterator();
			for (; iter.hasNext();) {
				Thread thread = iter.next();
				
				synchronized (thread) {
					thread.notify();
				}
			}
		}
	}
	
	private boolean sendLastChanges(HashMap<String, Long> lastids, HttpExchange exchange) {
		JSONArray jsonArray = new JSONArray();

		for (Iterator<String> channelnames = lastids.keySet().iterator(); channelnames.hasNext();) {
			String channelname = channelnames.next();
			
			if (channels.containsKey(channelname))  {
				synchronized (channels.get(channelname)) {

					if (channelCurrentMessageId.containsKey(channelname)) for (long i = lastids.get(channelname) + 1; i <= channelCurrentMessageId.get(channelname); i++) {
						JSONObject message = channelMessage.get(channelname).get(i);
						if (message != null) jsonArray.put(message);
					}
				}
			}
		}
		
		if (jsonArray.length() > 0) try {
			byte[] b = jsonArray.toString().getBytes("UTF-8");
					
			exchange.sendResponseHeaders(200, b.length);
			
			OutputStream responseBody = exchange.getResponseBody();
			
			responseBody.write(b, 0, b.length);
			return true;
		} catch (Exception $e) {}
		
		return false;
	}

	public void handle(HttpExchange exchange) throws IOException {	
		boolean wait = true;
		String requestMethod = exchange.getRequestMethod();
		
		BufferedReader is = new BufferedReader(new InputStreamReader(exchange.getRequestBody()));
		String post = is.readLine();

		URI uri = exchange.getRequestURI();
		String query = uri.getQuery(); // Check which channels to subscribe to
		
		HashMap<String,String> getParametersMap = new HashMap<String,String>();
		
		if (query != null) {
			String params[] = query.split("&");
		
			for (String param : params) {
			   String temp[] = param.split("=");
			   if (temp.length > 1)
					getParametersMap.put(temp[0], URLDecoder.decode(temp[1], "UTF-8"));
			}
		}
		
		HashMap<String,String> postParametersMap = new HashMap<String,String>();
		
		if (post != null) {
			String postparams[] = post.split("&");
		
			for (String param : postparams) {
			   String temp[] = param.split("=");
			   if (temp.length > 1)
					postParametersMap.put(temp[0], URLDecoder.decode(temp[1], "UTF-8"));
			}
		}
		
		Headers responseHeaders = exchange.getResponseHeaders();
		responseHeaders.set("Content-Type", "text/plain; charset=utf-8");
		
		OutputStream responseBody = exchange.getResponseBody();
		
		if (!postParametersMap.isEmpty()) {
			HashMap<String, Long> lastids = new HashMap<String, Long>();

			for (Iterator<String> channelnames = postParametersMap.keySet().iterator(); channelnames.hasNext();) {
				String channelname = channelnames.next();
				
				long channelvalue = Long.parseLong(postParametersMap.get(channelname));
				
				lastids.put(channelname, channelvalue);
			}
			
			if (getParametersMap.containsKey("last")) {
				int last = Integer.parseInt(getParametersMap.get("last"));
				
				JSONArray jsonArray = new JSONArray();
				
				for (Iterator<String> channelnames = postParametersMap.keySet().iterator(); channelnames.hasNext();) {
					String channelname = channelnames.next();
					
					if (channels.containsKey(channelname) && channelMessage.containsKey(channelname) && channelCurrentMessageId.containsKey(channelname))  {
						synchronized (channels.get(channelname)) {
							for (long i = channelCurrentMessageId.get(channelname) - last + 1; i <= channelCurrentMessageId.get(channelname); i++) {
								JSONObject message = channelMessage.get(channelname).get(i);
								if (message != null)
									jsonArray.put(message);
							}
						}
					}
				}
				
				if (jsonArray.length() > 0) {
					byte[] b = jsonArray.toString().getBytes("UTF-8");
								
					exchange.sendResponseHeaders(200, b.length);
					responseBody.write(b, 0, b.length);
					wait = false;
				}
			} else if (!lastids.isEmpty()) {
				wait = !sendLastChanges(lastids, exchange);
			} 
			
			if (wait) {
				Thread thisThread = Thread.currentThread();
				
				for (Iterator<String> channelnames = postParametersMap.keySet().iterator(); channelnames.hasNext();) {
					String channelname = channelnames.next();
					
					channels.putIfAbsent(channelname, new Vector<Thread>());
				
					synchronized(channels.get(channelname)) {
						if (!channels.get(channelname).contains(thisThread))
							channels.get(channelname).add(thisThread);
					}
				}
			
				while (wait) {
					synchronized(thisThread) {
						try {
							thisThread.wait();	
						} catch (Exception e) {}
					}
		
					wait = !sendLastChanges(lastids, exchange);
				}
				
				for (Iterator<String> channelnames = postParametersMap.keySet().iterator(); channelnames.hasNext();) {
					String channelname = channelnames.next();
					
					channels.putIfAbsent(channelname, new Vector<Thread>());
				
					synchronized(channels.get(channelname)) {
						if (channels.get(channelname).contains(thisThread))
							channels.get(channelname).remove(thisThread);
					}
				}
			}
			
						
		}
		
		responseBody.close();
	}
}