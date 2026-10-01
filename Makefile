# Konsep sama seperti docs/06 §6.5 — perintah di dalamnya mengikuti Laravel.
COMPOSE = docker compose -f docker-compose.prod.yml --env-file .env.docker
DEV_COMPOSE = docker compose -f docker-compose.yml
BACKUP_DIR = backups

.PHONY: env key init build up down restart ps logs migrate seed shell mysql \
	backup upgrade tunnel-up tunnel-down preview build-amd64

env: ## Salin contoh env (sekali saja, lalu isi .env.docker)
	@test -f .env.docker || cp .env.docker.example .env.docker
	@echo "Isi .env.docker lalu lanjutkan: make key"

key: ## Generate APP_KEY (tempel ke .env.docker / Dokploy Environment)
	docker build --target app -t afsana-app:tmp .
	docker run --rm afsana-app:tmp php artisan key:generate --show

init: build up migrate seed ## Alur penuh server baru: build + jalan + migrasi + seed

build: ## Build image native sesuai arch mesin ini
	$(COMPOSE) build app web

build-amd64: ## Build untuk server amd64 dari mesin lain lalu push (lihat docs/06 §6.3 Alur B)
	docker buildx build --platform linux/amd64 -t registry-contoh/jasa-app:latest --target app --push .
	docker buildx build --platform linux/amd64 -t registry-contoh/jasa-web:latest --target web --push .

up: ## Jalankan stack (tunggu healthy)
	$(COMPOSE) up -d --wait

down: ## Matikan stack
	$(COMPOSE) down

restart: ## Restart stack
	$(COMPOSE) restart

ps: ## Status kontainer
	$(COMPOSE) ps

logs: ## Log semua service (Ctrl-C untuk keluar)
	$(COMPOSE) logs -f

migrate: ## Jalankan migrasi Laravel
	$(COMPOSE) exec app php artisan migrate --force

seed: ## Seed admin + konten awal (idempoten)
	$(COMPOSE) exec app php artisan db:seed --force

shell: ## Masuk ke kontainer app
	$(COMPOSE) exec app sh

mysql: ## Masuk ke MySQL produksi
	$(COMPOSE) exec mysql mysql -u$${DB_USERNAME:?} -p$${DB_PASSWORD:?} $${DB_DATABASE:-jasa_website}

backup: ## Backup SQL + file upload ke backups/
	@mkdir -p $(BACKUP_DIR)
	$(COMPOSE) exec -T mysql mysqldump -u$${DB_USERNAME:?} -p$${DB_PASSWORD:?} $${DB_DATABASE:-jasa_website} > $(BACKUP_DIR)/db-$$(date +%Y%m%d-%H%M%S).sql
	docker run --rm -v afsana_uploads-data:/data:ro -v $$(pwd)/$(BACKUP_DIR):/out alpine tar -czf /out/uploads-$$(date +%Y%m%d-%H%M%S).tar.gz -C /data .
	@echo "Backup tersimpan di $(BACKUP_DIR)/"

upgrade: ## Tarik kode terbaru + rebuild + migrasi (produksi)
	git pull
	$(COMPOSE) build app web
	$(COMPOSE) up -d --wait
	$(COMPOSE) exec app php artisan migrate --force

tunnel-up: ## Nyalakan Cloudflare tunnel (butuh TUNNEL_TOKEN)
	$(COMPOSE) --profile tunnel up -d --wait cloudflared

tunnel-down: ## Matikan tunnel
	$(COMPOSE) --profile tunnel down cloudflared 2>/dev/null || $(COMPOSE) stop cloudflared

preview: ## Buka nginx sementara di 127.0.0.1:8080 (debug; tutup via `make down`)
	docker compose -f docker-compose.prod.yml -f docker-compose.preview.yml --env-file .env.docker up -d --wait web
	@echo "Buka http://127.0.0.1:8080 — tutup lagi dengan: make down"
