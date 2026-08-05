-- Supabase schema para Pasteleria Maria Angelica Espina
-- Ejecutar en Supabase > SQL Editor

create table if not exists pasteleria_site_content (
  id text primary key,
  content jsonb not null default '{}'::jsonb,
  updated_at timestamptz not null default now()
);

alter table pasteleria_site_content enable row level security;

drop policy if exists "pasteleria_site_content_select" on pasteleria_site_content;
drop policy if exists "pasteleria_site_content_insert" on pasteleria_site_content;
drop policy if exists "pasteleria_site_content_update" on pasteleria_site_content;
drop policy if exists "pasteleria_site_content_delete" on pasteleria_site_content;

create policy "pasteleria_site_content_select" on pasteleria_site_content
  for select using (true);

create policy "pasteleria_site_content_insert" on pasteleria_site_content
  for insert to anon, authenticated with check (true);

create policy "pasteleria_site_content_update" on pasteleria_site_content
  for update to anon, authenticated using (true) with check (true);

create policy "pasteleria_site_content_delete" on pasteleria_site_content
  for delete to anon, authenticated using (true);

insert into pasteleria_site_content (id, content)
values ('main', '{}'::jsonb)
on conflict (id) do nothing;

insert into storage.buckets (id, name, public)
values ('pasteleria', 'pasteleria', true)
on conflict (id) do update set public = true;

drop policy if exists "pasteleria_storage_select" on storage.objects;
drop policy if exists "pasteleria_storage_insert" on storage.objects;
drop policy if exists "pasteleria_storage_update" on storage.objects;
drop policy if exists "pasteleria_storage_delete" on storage.objects;

create policy "pasteleria_storage_select" on storage.objects
  for select using (bucket_id = 'pasteleria');

create policy "pasteleria_storage_insert" on storage.objects
  for insert to anon, authenticated with check (bucket_id = 'pasteleria');

create policy "pasteleria_storage_update" on storage.objects
  for update to anon, authenticated using (bucket_id = 'pasteleria') with check (bucket_id = 'pasteleria');

create policy "pasteleria_storage_delete" on storage.objects
  for delete to anon, authenticated using (bucket_id = 'pasteleria');
