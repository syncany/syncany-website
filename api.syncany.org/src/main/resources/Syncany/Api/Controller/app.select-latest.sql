select *
from app
where
  (`release`=1 or `release`=:release)
	and (os='all' or os=:os)
	and (arch='all' or arch=:arch)
  and appVersion = (
    select appVersion
    from app
    where `release`=1 or `release`=:release
    order by id desc
    limit 1
  )