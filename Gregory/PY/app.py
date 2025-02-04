import logging
from flask import Flask, request
from sqlalchemy import create_engine, event
import socket

# Configurer le logger
logging.basicConfig(filename='app.log', level=logging.INFO, 
                    format='%(asctime)s %(levelname)s %(message)s')

app = Flask(__name__)

# Configurer SQLAlchemy
engine = create_engine('sqlite:///example.db')

# Fonction pour déterminer si un VPN est utilisé (simplifiée)
def is_vpn():
    # Cette fonction pourrait vérifier l'adresse IP et utiliser un service externe pour déterminer si elle est liée à un VPN
    return False  # Placeholder pour la logique VPN réelle

# Fonction pour récupérer les informations du réseau et de l'appareil
def get_client_info():
    client_ip = request.remote_addr
    hostname = socket.gethostbyaddr(client_ip)[0] if client_ip else 'unknown'
    user_agent = request.headers.get('User-Agent', 'unknown')
    vpn_status = 'VPN' if is_vpn() else 'No VPN'
    return {
        'ip': client_ip,
        'hostname': hostname,
        'user_agent': user_agent,
        'vpn_status': vpn_status
    }

# Événement de connexion à la base de données
@event.listens_for(engine, "connect")
def log_db_connection(dbapi_connection, connection_record):
    client_info = get_client_info()
    logging.info(f"Connexion à la DB depuis {client_info['ip']} (Host: {client_info['hostname']}, "
                 f"User-Agent: {client_info['user_agent']}, VPN: {client_info['vpn_status']})")

# Exemple de route Flask qui se connecte à la DB
@app.route('/')
def index():
    try:
        # Connexion à la base de données
        with engine.connect() as connection:
            result = connection.execute("SELECT 1")
            return "Connection successful!"
    except Exception as e:
        logging.error(f"Erreur lors de la connexion à la DB: {str(e)}")
        return "Connection failed!"

if __name__ == '__main__':
    app.run(debug=True)
