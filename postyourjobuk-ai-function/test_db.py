import json
import mysql.connector

v = json.load(open("local.settings.json"))["Values"]
print("Connecting to:", v["DB_HOST"])

conn = mysql.connector.connect(
    host=v["DB_HOST"],
    user=v["DB_USER"],
    password=v["DB_PASSWORD"],
    database=v["DB_NAME"],
    connection_timeout=10,
)
cur = conn.cursor(dictionary=True)
cur.execute("SELECT * FROM applicants WHERE applicant_id = 1070")
row = cur.fetchone()
print("Row found:", row is not None)
if row:
    print("Columns:", list(row.keys()))
conn.close()