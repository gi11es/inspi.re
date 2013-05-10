#!/usr/bin/env ruby

require 'rubygems'
require 'eventmachine'
require 'json'
require 'evma_httpserver'
require 'cgi'
require 'socket'
require 'logger'


$logger = Logger.new('/var/log/echoserver/echoserver.log', 'daily')  
$channels = {}

class CommandServer < EventMachine::Connection
	def post_init
		super
		$logger.info("CommandServer opening connection")
		set_comm_inactivity_timeout(10)   
	end

	def receive_data data		
		begin
			memory_usage = `ps -o rss= -p #{$$}`.to_i
			$logger.info("CommandServer receive_data start Memory usage: #{memory_usage} | PID: #{$$}");
		
			json = JSON.parse data
			if json.has_key?('command')
				case json['command']
				when 'publish'
					if json.has_key?('text') && json.has_key?('channel')
						publish(json['channel'], json['text'])
					end
				else
					send_data("Unknown command #{json['command']}\n")
				end
			end
			
			memory_usage = `ps -o rss= -p #{$$}`.to_i
			$logger.info("CommandServer receive_data end Memory usage: #{memory_usage} | PID: #{$$}");
		rescue JSON::ParserError => e
			send_data("Invalid command, please use JSON syntax\n")
		end
	end
	
	def publish(channel, text)
		$logger.debug("Publishing \"#{text}\" to channel #{channel}")
		
		if !$channels.has_key?(channel)
			$channels[channel] = EventMachine::Channel.new
		end
		
		$channels[channel].push text
	end
	
	def unbind
		super
		$logger.info("CommandServer closing connection")
	end
end

class SubscriberServer < EventMachine::Connection
	include EventMachine::HttpServer

	def post_init
		super
		$logger.info("SubscriberServer opening connection")
		@subscriptions = {}
		@sending_response = false
	end
	
	def unbind
		super
		$logger.info("SubscriberServer closing connection")
		@subscriptions.each do | channel, subscription |
			$channels[channel].unsubscribe(subscription)
		end		
  	end
  	
  	def parse_params
		params = ENV['QUERY_STRING'].split('&').inject({}) {|p, s| k,v=s.split('=');p[k.to_s]=CGI.unescape(v.to_s);p}
		params
	end 
	
	def subscribe(channel)
		if !$channels.has_key?(channel)
			$channels[channel] = EventMachine::Channel.new
		end
		
		@subscriptions[channel] = $channels[channel].subscribe do |msg| 
			if !@sending_reponse
				@sending_response = true
				answer = {}
				answer['channel'] = channel
				answer['text'] = msg
				
				@response.headers['Content-Type'] = 'text/plain'
				@response.status = 200
				@response.content = JSON.generate answer
				@response.send_response
				@sending_response = false
			end
		end
	end
  	
  	def process_http_request
  		memory_usage = `ps -o rss= -p #{$$}`.to_i
		$logger.info("process_http_request start Memory usage: #{memory_usage} | PID: #{$$}");
		
  		@response = EventMachine::DelegatedHttpResponse.new( self )
  		
  		action = ENV['PATH_INFO']

  		params = parse_params
  		
  		case action
  		when '/http-bind'
  			if params.has_key?('channels')
  				channels_to_subscribe = params['channels'].split(',')
  				channels_to_subscribe.each do | channel |
  					if !@subscriptions.has_key?(channel)
  						subscribe(channel)
  					end
  				end
  			else
				@response.headers['Content-Type'] = 'text/html'
				@response.status  = 404
				@response.content = %|<h1>Not Found</h1>"|
				@response.send_response 
  			end
  		else
  			@response.headers['Content-Type'] = 'text/html'
  			@response.status  = 404
  			@response.content = %|<h1>Not Found</h1>"|
     		@response.send_response 
 		end

		memory_usage = `ps -o rss= -p #{$$}`.to_i
		$logger.info("process_http_request end Memory usage: #{memory_usage} | PID: #{$$}");
  	end
end	

pid = fork {
	EventMachine::run do
		Signal.trap('HUP', 'IGNORE')
		trap("INT") { EventMachine.stop }
		trap("TERM") { EventMachine.stop }
		
		EventMachine.epoll
	
		host = '0.0.0.0'
		port = 8222
		EventMachine::start_server host, port, CommandServer
		$logger.info("Started CommandServer on #{host}:#{port}...")
		
		host = '0.0.0.0'
		port = 8280
		EventMachine::start_server host, port, SubscriberServer
		$logger.info("Started SubscriberServer on #{host}:#{port}...")
	end
}

Process.detach(pid)
