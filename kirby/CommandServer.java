import java.io.*;
import java.util.Iterator; 
import java.util.Vector; 
import java.net.*;
import org.json.JSONException;
import org.json.JSONObject;

public class CommandServer extends Thread {
	private Socket clientSocket;
	private BufferedReader is;
	private PollHandler pollHandler;

	public CommandServer(PollHandler ph, Socket csocket) {
    	clientSocket = csocket;
    	pollHandler = ph;
  	}
  	
  	public void cleanup() {
  		try {
			is.close();
			clientSocket.close();
		} catch (IOException e) {}
  	}
  	
  	public void broadcast(String channel, String text) {
		pollHandler.write(channel, text);
  	}
  	
	public void run() {
		boolean keepReading = true;
		String input;
		
		try {
			is = new BufferedReader(new InputStreamReader(clientSocket.getInputStream()));
		} catch (IOException e) {
			keepReading = false;
		}
		
		try {
			while ((input = is.readLine()) != null) {
				JSONObject command = new JSONObject(input);
				if (command.has("command") && command.has("channel") && command.has("text") && command.getString("command").equals("publish")) {
					broadcast(command.getString("channel"), command.getString("text"));
				}
			}
		} catch (IOException e) {} 
		catch (JSONException f) {}
		
		cleanup();
	}
}