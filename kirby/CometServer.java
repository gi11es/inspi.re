import java.io.*;
import java.util.concurrent.Executors;
import java.net.*;
import com.sun.net.httpserver.HttpServer;

public class CometServer {
	private static ServerSocket commandServerSocket;
	private static ServerSocket pollServerSocket;
	private static CommandServer cs;
	private static PollHandler ph;

	public static void main(String args[]) {
		try {
			commandServerSocket = new ServerSocket(8222);
			commandServerSocket.setSoTimeout(100);
			
			InetSocketAddress addr = new InetSocketAddress(8280);
			HttpServer server = HttpServer.create(addr, 0);
			
			ph = new PollHandler();
			
			server.createContext("/http-bind", ph);
			server.setExecutor(Executors.newCachedThreadPool());
			server.start();
		} catch (IOException e) {
			System.exit(0);
		}		

		while (true) {
			try {
				Socket clientSocket = commandServerSocket.accept();
				cs = new CommandServer(ph, clientSocket);
				cs.start();
			} catch (IOException e) {}
		}
	}
}